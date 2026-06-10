# Privacy and GDPR

Tools for privacy compliance and member data rights.

[← Back to features](README.md)

## Privacy policy

- Public privacy policy page with configurable controller name and contact email
- New users must accept the policy before accessing the platform
- Policy version tracked per user

## Data export

Members can download their personal data as JSON from **Settings → Privacy**.

## Cookie consent

A cookie consent banner is shown to visitors.

## Configuration

Set in `.env`:

```dotenv
GDPR_CONTROLLER_NAME="Your Organization"
GDPR_CONTACT_EMAIL=privacy@example.org
GDPR_POLICY_VERSION=1.0
```

User-facing privacy controls are in [User settings](user-settings.md).
