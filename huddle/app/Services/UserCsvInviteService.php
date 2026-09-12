<?php

namespace App\Services;

use App\Models\MembershipRenewal;
use App\Models\Role;
use App\Models\User;
use App\Models\UserFlags;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;

class UserCsvInviteService
{
    /** @var list<string> */
    private const REQUIRED_HEADERS = ['name', 'email'];

    public function __construct(
        private readonly UserInvitationService $invitations,
    ) {}

    /**
     * @return array{rows: list<array<string, mixed>>, valid_count: int, invalid_count: int}
     */
    public function parse(string $filePath): array
    {
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new InvalidArgumentException(__('Could not read CSV file.'));
        }

        $headerRow = fgetcsv($handle);

        if ($headerRow === false) {
            fclose($handle);

            throw new InvalidArgumentException(__('CSV file is empty.'));
        }

        $headers = $this->normalizeHeaders($headerRow);
        $this->validateRequiredHeaders($headers);

        $rolesByName = $this->lookupTable(Role::query()->pluck('id', 'name'));
        $flagsByName = $this->lookupTable(UserFlags::query()->pluck('id', 'name'));
        $membershipsByName = $this->lookupTable(MembershipRenewal::query()->pluck('id', 'name'));
        $existingEmails = User::query()
            ->pluck('email')
            ->mapWithKeys(fn (string $email) => [Str::lower($email) => true])
            ->all();

        $rows = [];
        $seenEmails = [];
        $line = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $line++;

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $rows[] = $this->parseRow(
                $line,
                $data,
                $headers,
                $rolesByName,
                $flagsByName,
                $membershipsByName,
                $existingEmails,
                $seenEmails,
            );
        }

        fclose($handle);

        $validCount = count(array_filter($rows, fn (array $row) => $row['valid']));

        return [
            'rows' => $rows,
            'valid_count' => $validCount,
            'invalid_count' => count($rows) - $validCount,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{invited: int, failed: list<array<string, mixed>>}
     */
    public function importValidRows(array $rows): array
    {
        $invited = 0;
        $failed = [];

        foreach ($rows as $row) {
            if (! $row['valid']) {
                continue;
            }

            try {
                $this->invitations->invite(
                    $row['name'],
                    $row['email'],
                    $row['role_ids'],
                    $row['flag_ids'],
                    $row['membership_renewal_id'],
                );
                $invited++;
            } catch (\Throwable $exception) {
                $failed[] = [
                    'line' => $row['line'],
                    'email' => $row['email'],
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return [
            'invited' => $invited,
            'failed' => $failed,
        ];
    }

    /**
     * @param  list<string>  $headerRow
     * @return array<string, int>
     */
    private function normalizeHeaders(array $headerRow): array
    {
        $headers = [];

        foreach ($headerRow as $index => $header) {
            $normalized = Str::of((string) $header)
                ->trim()
                ->lower()
                ->replace(' ', '_')
                ->toString();

            if ($normalized === 'membership_period') {
                $normalized = 'membership';
            }

            if ($normalized === 'flags') {
                $normalized = 'tags';
            }

            $headers[$normalized] = $index;
        }

        return $headers;
    }

    /**
     * @param  array<string, int>  $headers
     */
    private function validateRequiredHeaders(array $headers): void
    {
        $missing = array_diff(self::REQUIRED_HEADERS, array_keys($headers));

        if ($missing !== []) {
            throw new InvalidArgumentException(__('CSV is missing required columns: :columns', [
                'columns' => implode(', ', $missing),
            ]));
        }
    }

    /**
     * @param  list<string|null>  $data
     * @param  array<string, int>  $headers
     * @param  array<string, array{id: int, name: string}>  $rolesByName
     * @param  array<string, array{id: int, name: string}>  $flagsByName
     * @param  array<string, array{id: int, name: string}>  $membershipsByName
     * @param  array<string, true>  $existingEmails
     * @param  array<string, true>  $seenEmails
     * @return array<string, mixed>
     */
    private function parseRow(
        int $line,
        array $data,
        array $headers,
        array $rolesByName,
        array $flagsByName,
        array $membershipsByName,
        array &$existingEmails,
        array &$seenEmails,
    ): array {
        $name = trim((string) ($data[$headers['name']] ?? ''));
        $email = Str::lower(trim((string) ($data[$headers['email']] ?? '')));
        $rolesInput = $this->columnValue($data, $headers, 'roles');
        $tagsInput = $this->columnValue($data, $headers, 'tags');
        $membershipInput = $this->columnValue($data, $headers, 'membership');

        $errors = [];

        $validator = Validator::make(
            ['name' => $name, 'email' => $email],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255'],
            ],
        );

        if ($validator->fails()) {
            $errors = array_merge($errors, $validator->errors()->all());
        }

        if ($email !== '' && isset($seenEmails[$email])) {
            $errors[] = __('Duplicate email in CSV file.');
        }

        if ($email !== '' && isset($existingEmails[$email])) {
            $errors[] = __('A user with this email already exists.');
        }

        if ($email !== '') {
            $seenEmails[$email] = true;
        }

        $roleNames = $this->parseList($rolesInput);
        if ($roleNames === []) {
            $roleNames = ['member'];
        }

        $roleIds = [];
        $resolvedRoleNames = [];

        foreach ($roleNames as $roleName) {
            $lookupKey = Str::lower($roleName);

            if (! isset($rolesByName[$lookupKey])) {
                $errors[] = __('Unknown role: :role', ['role' => $roleName]);

                continue;
            }

            $roleIds[] = $rolesByName[$lookupKey]['id'];
            $resolvedRoleNames[] = $rolesByName[$lookupKey]['name'];
        }

        $roleIds = array_values(array_unique($roleIds));

        if ($roleIds === [] && $errors === []) {
            $errors[] = __('At least one valid role is required.');
        }

        $flagIds = [];
        $resolvedTagNames = [];

        foreach ($this->parseList($tagsInput) as $tagName) {
            $lookupKey = Str::lower($tagName);

            if (! isset($flagsByName[$lookupKey])) {
                $errors[] = __('Unknown tag: :tag', ['tag' => $tagName]);

                continue;
            }

            $flagIds[] = $flagsByName[$lookupKey]['id'];
            $resolvedTagNames[] = $flagsByName[$lookupKey]['name'];
        }

        $flagIds = array_values(array_unique($flagIds));

        $membershipRenewalId = null;
        $resolvedMembership = '';

        if ($membershipInput !== '') {
            $lookupKey = Str::lower($membershipInput);

            if (! isset($membershipsByName[$lookupKey])) {
                $errors[] = __('Unknown membership period: :period', ['period' => $membershipInput]);
            } else {
                $membershipRenewalId = $membershipsByName[$lookupKey]['id'];
                $resolvedMembership = $membershipsByName[$lookupKey]['name'];
            }
        }

        return [
            'line' => $line,
            'name' => $name,
            'email' => $email,
            'roles_display' => $resolvedRoleNames !== [] ? implode('; ', $resolvedRoleNames) : ($rolesInput !== '' ? $rolesInput : 'member'),
            'tags_display' => $resolvedTagNames !== [] ? implode('; ', $resolvedTagNames) : $tagsInput,
            'membership_display' => $resolvedMembership !== '' ? $resolvedMembership : $membershipInput,
            'role_ids' => $roleIds,
            'flag_ids' => $flagIds,
            'membership_renewal_id' => $membershipRenewalId,
            'errors' => $errors,
            'valid' => $errors === [] && $roleIds !== [],
        ];
    }

    /**
     * @param  list<string|null>  $data
     * @param  array<string, int>  $headers
     */
    private function columnValue(array $data, array $headers, string $column): string
    {
        if (! isset($headers[$column])) {
            return '';
        }

        return trim((string) ($data[$headers[$column]] ?? ''));
    }

    /**
     * @return list<string>
     */
    private function parseList(string $value): array
    {
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/[;,]/', $value) ?: [])));
    }

    /**
     * @param  list<string|null>  $data
     */
    private function isEmptyRow(array $data): bool
    {
        foreach ($data as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  Collection<string|int, string|int>  $items
     * @return array<string, array{id: int, name: string}>
     */
    private function lookupTable(Collection $items): array
    {
        $map = [];

        foreach ($items as $name => $id) {
            $map[Str::lower((string) $name)] = [
                'id' => (int) $id,
                'name' => (string) $name,
            ];
        }

        return $map;
    }
}
