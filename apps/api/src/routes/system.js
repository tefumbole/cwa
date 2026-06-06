import { Router } from 'express';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { requireAuth } from '../middleware/auth.js';

const router = Router();
const __dirname = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(__dirname, '../../../../');
const apiEnvPath = path.join(repoRoot, 'apps/api/.env');
const frontendEnvPaths = [
  path.join(repoRoot, '.env.local'),
  path.join(repoRoot, '.env'),
];

function isAdmin(user) {
  return user?.role === 'super_admin' || user?.role === 'admin';
}

function readFrontendEnv() {
  for (const p of frontendEnvPaths) {
    if (fs.existsSync(p)) return fs.readFileSync(p, 'utf8');
  }
  const example = path.join(repoRoot, '.env.local.example');
  if (fs.existsSync(example)) return fs.readFileSync(example, 'utf8');
  return '';
}

function writeFrontendEnv(content) {
  const target = fs.existsSync(path.join(repoRoot, '.env.local'))
    ? path.join(repoRoot, '.env.local')
    : path.join(repoRoot, '.env');
  fs.writeFileSync(target, content, 'utf8');
  return target;
}

router.get('/env-files', requireAuth, (req, res) => {
  if (!isAdmin(req.user)) {
    return res.status(403).json({ error: 'Admin access required' });
  }

  const api = fs.existsSync(apiEnvPath)
    ? fs.readFileSync(apiEnvPath, 'utf8')
    : (fs.existsSync(path.join(repoRoot, 'apps/api/.env.local.example'))
      ? fs.readFileSync(path.join(repoRoot, 'apps/api/.env.local.example'), 'utf8')
      : '');

  res.json({ frontend: readFrontendEnv(), api });
});

router.put('/env-files', requireAuth, (req, res) => {
  if (!isAdmin(req.user)) {
    return res.status(403).json({ error: 'Admin access required' });
  }

  const { frontend, api } = req.body || {};
  try {
    if (typeof frontend === 'string') writeFrontendEnv(frontend);
    if (typeof api === 'string') fs.writeFileSync(apiEnvPath, api, 'utf8');
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

export default router;
