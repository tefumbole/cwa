# Beyond Company Ltd (BeyondTechWorld)

Web application for **Beyond Company Ltd**, deployed at [beyondtechworld.com](https://beyondtechworld.com).

## Local development

```bash
npm run dev:local
```

## Deploy to VPS (beyondtechworld.com)

On the VPS:

```bash
cd /var/www/beyondtechworld
bash tools/deploy-beyondtechworld-vps.sh
```

From your Mac (after pushing to GitHub):

```bash
ssh myvps "cd /var/www/beyondtechworld && git pull && bash tools/deploy-beyondtechworld-vps.sh"
```

## Branding

- Company name: **Beyond Company Ltd**
- WhatsApp contact: **+237 675 321 739**
- Configure via `.env`: `VITE_COMPANY_NAME`, `VITE_ADMIN_PHONE_NUMBER`, `VITE_SITE_URL`
