# Alpha Bridge ERP restore points

Each restore point is a **git tag** plus a short manifest in this folder.

| Version | Tag | Notes |
|---------|-----|-------|
| ABT_ERP_V.1.1.2 | `ABT_ERP_V.1.1.2` | Custom role permissions, signed agreements, version auto-bump |
| ABT_ERP_V.1.1.1 | `ABT_ERP_V.1.1.1` | WhatsApp queue, i18n FR/EN, signed agreements, RBAC |

Current version is defined in `src/constants/appVersion.js` and shown on the login page.
Enable auto bump on push: `bash tools/setup-git-hooks.sh`
