<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    public const PROJECTS_EDIT_ANY = 'projects.edit_any';

    public const PROJECTS_DELETE_ANY = 'projects.delete_any';

    public const EVENTS_EDIT_ANY = 'events.edit_any';

    public const EVENTS_DELETE_ANY = 'events.delete_any';

    public const WIKI_EDIT = 'wiki.edit';

    public const FORMS_MANAGE = 'forms.manage';

    public const ACCREDITATIONS_ASSIGN_VIA_EXAM = 'accreditations.assign_via_exam';

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return [
            self::PROJECTS_EDIT_ANY,
            self::PROJECTS_DELETE_ANY,
            self::EVENTS_EDIT_ANY,
            self::EVENTS_DELETE_ANY,
            self::WIKI_EDIT,
            self::FORMS_MANAGE,
            self::ACCREDITATIONS_ASSIGN_VIA_EXAM,
        ];
    }

    protected $fillable = [
        'slug',
        'name',
        'description',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission')
            ->withTimestamps();
    }
}
