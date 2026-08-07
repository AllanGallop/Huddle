# Forms

Collect information from members through surveys and knowledge checks.

[← Back to features](README.md)

## For all members

- Browse published forms
- Complete surveys and exams
- View your own submission history and exam results

## For form managers

Users with **Manage forms** (or admin) can:

- Create and edit forms with multiple question types
- Publish or unpublish forms
- Review all submissions per form

## Form types

| Type | Purpose |
|------|---------|
| **Survey** | Information gathering |
| **Exam** | Scored assessment with a configurable pass percentage |

## Exams and accreditations

Users with **Assign exam to accreditation** (or admin) can optionally link an exam to an accreditation when editing the form.

- Members who **pass** the exam are automatically given that accreditation (idempotent if they already hold it)
- Failing the exam does not change assignments
- Manual assignments remain available under [Manage accreditations](mentors.md)

## Question types

Yes/no and multiple choice — with correct-answer marking and points for exams.

## Permissions

Form management requires the **Manage forms** permission or the **admin** role. Linking exams to accreditations additionally requires **Assign exam to accreditation**. See [Roles and permissions](roles-and-permissions.md).
