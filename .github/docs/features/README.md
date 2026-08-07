# Features

Huddle is built for community organisations — makerspaces, clubs, and volunteer groups — that need to coordinate projects, run events, track membership, and share knowledge in one place.

Screenshots are in [`.github/images`](../../images/) where noted below.

## Platform overview

The sidebar groups day-to-day tools under **Platform**:

| Area | Route | Guide | Who can access |
|------|-------|-------|----------------|
| Dashboard | `/dashboard` | [Dashboard](dashboard.md) | All members |
| Projects | `/projects` | [Projects](projects.md) | All members |
| Reports | `/reports` | [Reports](reports.md) | All members; financial columns for Committee and admins |
| Events | `/events` | [Events](events.md) | All members |
| Members | `/members` | [Members](members.md) | All members |
| Forms | `/forms` | [Forms](forms.md) | All members; management via **Manage forms** permission |
| Wiki | `/wiki` | [Wiki](wiki.md) | All members; editing via **Edit wiki** permission |
| Accreditations | `/accreditations` | [Accreditations](accreditations.md) | All members |
| Manage accreditations | `/mentors` | [Mentors](mentors.md) | **Assign exam to accreditation** or admins |
| Admin | `/admin` | [Admin](admin.md) | Admins only |

User settings are available from the account menu — see [User settings](user-settings.md).

## Roles and permissions

Users can hold **multiple roles**. Permissions are unioned; the **admin** role bypasses all gates. Admins define roles and tick capabilities under [Admin → Roles](admin.md#roles).

See [Roles and permissions](roles-and-permissions.md) for the full permission list and ownership rules.

## Platform services

| Topic | Guide |
|-------|-------|
| Community digest | [community-digest.md](community-digest.md) |
| Privacy and GDPR | [privacy-and-gdpr.md](privacy-and-gdpr.md) |
| First-time setup | [setup.md](setup.md) |

## Related documentation

| Guide | Description |
|-------|-------------|
| [Deployment build](../deployment.md) | GitHub Actions release package and FTP deploy |
| [Docker install guide](../docker-install-guide.md) | Run locally with Docker |
| [Hosting guide](../hosting-guide.md) | Deploy on Apache/PHP |
| [Development guide](../development.md) | Local dev workflow and testing |
