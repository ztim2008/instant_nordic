import { mkdirSync, cpSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { build } from 'esbuild';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.resolve(__dirname, '../..');
const tempDir = path.join(__dirname, 'dist');
const targets = [
  path.join(rootDir, 'templates/default/controllers/nordicstyl'),
  path.join(rootDir, 'nordicstyl.install.0.1.0/package/templates/default/controllers/nordicstyl')
];

mkdirSync(tempDir, { recursive: true });

await build({
  entryPoints: [path.join(__dirname, 'src/main.jsx')],
  bundle: true,
  outfile: path.join(tempDir, 'editor.js'),
  format: 'iife',
  globalName: 'NordicEditorShell',
  platform: 'browser',
  target: ['es2020'],
  jsx: 'automatic',
  loader: {
    '.svg': 'dataurl'
  },
  minify: false,
  sourcemap: false,
  legalComments: 'none'
});

for (const target of targets) {
  mkdirSync(path.join(target, 'js'), { recursive: true });
  mkdirSync(path.join(target, 'css'), { recursive: true });
  cpSync(path.join(tempDir, 'editor.js'), path.join(target, 'js/editor.js'));
  cpSync(path.join(tempDir, 'editor.css'), path.join(target, 'css/editor.css'));
}