<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl" class="inline-flex items-center gap-2.5">
                <span class="flex size-10 items-center justify-center rounded-xl bg-huddle-primary/15 text-huddle-primary">
                    <x-material-icon name="storefront" class="text-[1.5rem]" />
                </span>
                {{ __('Customers') }}
            </flux:heading>
            <flux:text class="mt-1">{{ __('People or organisations that projects are delivered for.') }}</flux:text>
        </div>
        <flux:button variant="primary" wire:click="openCreateCustomerModal">
            <span class="inline-flex items-center gap-2">
                <x-material-icon name="add" class="text-[1.25rem]" />
                {{ __('Add customer') }}
            </span>
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-huddle-comp/40 bg-huddle-comp/10 px-4 py-3 text-sm text-zinc-800 dark:text-zinc-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded-xl border border-zinc-200 border-s-4 border-s-huddle-primary bg-zinc-50/80 p-4 dark:border-zinc-700 dark:border-s-huddle-primary dark:bg-zinc-800/40 sm:p-5">
        <div class="w-full sm:max-w-md">
            <flux:input
                wire:model.live.debounce.300ms="search"
                :label="__('Search')"
                :placeholder="__('Name, email, telephone, or address...')"
            >
                <x-slot:iconLeading>
                    <x-material-icon name="search" class="text-[1.25rem] text-zinc-400" />
                </x-slot:iconLeading>
            </flux:input>
        </div>
        <flux:text class="mt-3 text-sm text-zinc-500">
            {{ trans_choice(':count customer|:count customers', $this->customers->count(), ['count' => $this->customers->count()]) }}
        </flux:text>
    </div>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:shadow-none">
        @if ($this->customers->isEmpty())
            <div class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                <div class="flex size-14 items-center justify-center rounded-full bg-huddle-primary/10 text-huddle-primary">
                    <x-material-icon name="storefront" class="text-[2rem]" />
                </div>
                <flux:heading size="lg">
                    {{ filled(trim($search)) ? __('No matching customers') : __('No customers yet') }}
                </flux:heading>
                <flux:text>
                    {{ filled(trim($search)) ? __('Try a different search term.') : __('Add a customer to link them to projects.') }}
                </flux:text>
                @unless (filled(trim($search)))
                    <flux:button variant="primary" wire:click="openCreateCustomerModal">
                        <span class="inline-flex items-center gap-2">
                            <x-material-icon name="add" class="text-[1.25rem]" />
                            {{ __('Add customer') }}
                        </span>
                    </flux:button>
                @endunless
            </div>
        @else
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700">
                    <tr>
                        <th class="px-5 py-3">{{ __('Name') }}</th>
                        <th class="px-5 py-3">{{ __('Type') }}</th>
                        <th class="px-5 py-3 hidden md:table-cell">{{ __('Email') }}</th>
                        <th class="px-5 py-3 hidden lg:table-cell">{{ __('Telephone') }}</th>
                        <th class="px-5 py-3 hidden xl:table-cell">{{ __('Address') }}</th>
                        <th class="px-5 py-3">{{ __('Projects') }}</th>
                        <th class="px-5 py-3 text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($this->customers as $customer)
                        <tr wire:key="customer-{{ $customer->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-5 py-3 font-medium text-zinc-900 dark:text-white">{{ $customer->name }}</td>
                            <td class="px-5 py-3">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-huddle-primary/15 text-huddle-primary' => $customer->type === 'commercial',
                                    'bg-huddle-alt/25 text-amber-900 dark:text-huddle-alt' => $customer->type === 'domestic',
                                ])>
                                    {{ $customer->typeLabel() }}
                                </span>
                            </td>
                            <td class="hidden px-5 py-3 text-zinc-600 dark:text-zinc-300 md:table-cell">{{ $customer->email ?: '—' }}</td>
                            <td class="hidden px-5 py-3 text-zinc-600 dark:text-zinc-300 lg:table-cell">{{ $customer->telephone ?: '—' }}</td>
                            <td class="hidden max-w-xs truncate px-5 py-3 text-zinc-600 dark:text-zinc-300 xl:table-cell" title="{{ $customer->address }}">
                                {{ $customer->address ?: '—' }}
                            </td>
                            <td class="px-5 py-3 text-zinc-600 dark:text-zinc-300">{{ $customer->projects_count }}</td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-1">
                                    <flux:button size="sm" variant="ghost" wire:click="openEditCustomerModal({{ $customer->id }})">
                                        <x-material-icon name="edit" class="text-[1rem]" />
                                    </flux:button>
                                    <flux:button
                                        size="sm"
                                        variant="danger"
                                        wire:click="deleteCustomer({{ $customer->id }})"
                                        wire:confirm="{{ __('Delete customer :name? Linked projects will keep running without a customer.', ['name' => $customer->name]) }}"
                                    >
                                        <x-material-icon name="delete" class="text-[1rem]" />
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <flux:modal wire:model="showCustomerModal" class="md:max-w-2xl">
        <form wire:submit="saveCustomer" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingCustomerId ? __('Edit customer') : __('Add customer') }}
                </flux:heading>
                <flux:text class="mt-1">{{ __('Contact details for the person or organisation a project is for.') }}</flux:text>
            </div>

            <flux:input wire:model="customer_name" :label="__('Name')" required />

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:select wire:model="customer_type" :label="__('Type')" required>
                    <flux:select.option value="domestic">{{ __('Domestic') }}</flux:select.option>
                    <flux:select.option value="commercial">{{ __('Commercial') }}</flux:select.option>
                </flux:select>
                <flux:input wire:model="customer_telephone" type="tel" :label="__('Telephone')" />
            </div>

            <flux:input wire:model="customer_email" type="email" :label="__('Email')" />
            <flux:textarea wire:model="customer_address" :label="__('Address')" rows="3" />

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeCustomerModal">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingCustomerId ? __('Save changes') : __('Create customer') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
