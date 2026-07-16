#!/usr/bin/env node
/**
 * Increment CWA patch version (CWA V x.y.z -> z+1).
 * Source of truth: laravel-app/VERSION
 * Also syncs Node APP_VERSION constants to CWA_V.x.y.z
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..');

const VERSION_FILE = path.join(ROOT, 'laravel-app/VERSION');
const JS_VERSION_FILES = [
  path.join(ROOT, 'src/constants/appVersion.js'),
  path.join(ROOT, 'apps/api/src/constants/appVersion.js'),
];

const DISPLAY_RE = /^CWA V (\d+)\.(\d+)\.(\d+)$/;

function parseDisplay(version) {
  const match = String(version).trim().match(DISPLAY_RE);
  if (!match) throw new Error(`Invalid VERSION format (expected "CWA V x.y.z"): ${version}`);
  return {
    major: Number(match[1]),
    minor: Number(match[2]),
    patch: Number(match[3]),
  };
}

function toDisplay({ major, minor, patch }) {
  return `CWA V ${major}.${minor}.${patch}`;
}

function toTag({ major, minor, patch }) {
  return `CWA_V.${major}.${minor}.${patch}`;
}

function bumpParts(parts) {
  return { ...parts, patch: parts.patch + 1 };
}

function readCurrent() {
  if (!fs.existsSync(VERSION_FILE)) {
    return { major: 1, minor: 1, patch: 1 };
  }
  return parseDisplay(fs.readFileSync(VERSION_FILE, 'utf8'));
}

function writeVersionFile(display) {
  fs.writeFileSync(VERSION_FILE, `${display}\n`);
}

function syncJsFiles(tag) {
  for (const filePath of JS_VERSION_FILES) {
    if (!fs.existsSync(filePath)) continue;
    let content = fs.readFileSync(filePath, 'utf8');
    if (/export const APP_VERSION = '[^']+';/.test(content)) {
      content = content.replace(
        /export const APP_VERSION = '[^']+';/,
        `export const APP_VERSION = '${tag}';`
      );
    } else {
      content = `export const APP_VERSION = '${tag}';\n`;
    }
    fs.writeFileSync(filePath, content);
  }
}

function main() {
  const currentParts = readCurrent();
  const nextParts = bumpParts(currentParts);
  const currentDisplay = toDisplay(currentParts);
  const nextDisplay = toDisplay(nextParts);
  const nextTag = toTag(nextParts);

  writeVersionFile(nextDisplay);
  syncJsFiles(nextTag);

  console.log(`${currentDisplay} -> ${nextDisplay}`);
  return nextDisplay;
}

// Allow: node tools/bump-version.js --set "CWA V 1.1.1"
const setIdx = process.argv.indexOf('--set');
if (setIdx !== -1 && process.argv[setIdx + 1]) {
  const display = process.argv[setIdx + 1].trim();
  const parts = parseDisplay(display);
  writeVersionFile(toDisplay(parts));
  syncJsFiles(toTag(parts));
  console.log(`set ${toDisplay(parts)}`);
} else {
  main();
}
