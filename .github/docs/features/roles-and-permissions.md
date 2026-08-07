# Roles and permissions

Huddle uses **multi-role permissions** plus optional **tags** (user flags). Users can hold more than one role; effective capabilities are the **union** of those roles’ permissions. The **admin** role bypasses all permission gates.

[← Back to features](README.md)

## Roles

Admins manage roles under [Admin → Roles](admin.md#roles): create custom roles, tick which permissions each grants, and assign one or more roles to each user.

| System role | Description |
|-------------|-------------|
| **Admin** | Full access (admin panel and all capabilities). Cannot be deleted; always bypasses permission checks. |
| **Member** | Default role for regular users; no elevated permissions. Cannot be deleted. |
| **Mentor** (seeded) | Wiki editing, form management, and accreditation / exam linking. Existing Mentor-tag users are migrated onto this role on upgrade. |

Custom roles can combine any of the permissions below.

## Permissions

| Permission | What it allows |
|------------|----------------|
| **Edit any project** | Edit projects created by others |
| **Delete any project** | Delete projects created by others |
| **Edit any event** | Edit events created by others |
| **Delete any event** | Delete events created by others |
| **Edit wiki** | Create and edit [wiki](wiki.md) pages |
| **Manage forms** | Create forms and edit any [form](forms.md) |
| **Assign exam to accreditation** | Link exams to accreditations, and manage accreditation types / assignments under [Manage accreditations](mentors.md) |

Without these permissions, members can still create and manage **their own** projects and events (see ownership below).

## Tags

Tags are flexible labels assigned by admins. They are **not** the primary auth model (except Committee for reports).

| Tag | Notes |
|-----|--------|
| **Mentor** | Display / filtering only after the roles upgrade; authz uses the Mentor **role** (or equivalent permissions) |
| **Committee** | View financial columns on [project reports](reports.md) |
| **Keyholder**, **Trustee**, etc. | Displayed on profiles; used for filtering and identification |

Admins manage tags from the [Admin](admin.md) panel.

## Project and event ownership

- **Creators** can edit and delete their own projects/events, upload project images, and (for events) manage the volunteer roster when they are organisers.
- **Edit any / Delete any** permissions extend those actions to other people’s projects or events.
- **Admins** can always manage volunteers and assign project leaders.
- **Project leaders** (or admins) can manage the Finance tab on [projects](projects.md) (quotes, invoices, payment tracking).

## Related areas

| Area | Access |
|------|--------|
| [Admin](admin.md) | Admin role only |
| [Manage accreditations](mentors.md) | Admin, or **Assign exam to accreditation** |
| [Accreditations directory](accreditations.md) | All members |
| [Forms](forms.md) management | Admin, or **Manage forms** |
| [Wiki](wiki.md) editing | Admin, or **Edit wiki** |
