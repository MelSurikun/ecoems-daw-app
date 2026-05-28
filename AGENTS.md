# AGENTS.md

## Stack
- PHP 8 (vanilla, no composer), MariaDB, vanilla JS, CSS3
- CDN: Chart.js 4.4.0, Leaflet 1.9.4, Google Fonts (Sora + IBM Plex Serif)

## Structure
| Path | Purpose |
|---|---|---|
| `frontend/*.php` | 7 pages: index, escuela, comparar, mapa, resumen, **planteles**, acerca |
| `frontend/includes/navbar.php` | Shared navbar (SVG logo + nav links) — included by all 7 pages |
| `backend/config.php` | `getDB()` PDO singleton — credentials are placeholders (`ecoems_user` / `password` on `localhost`) |
| `backend/api/escuela.php` | GET `?plantel=B00001` (stats) or `?q=CETIS` (autocomplete, max 20) |
| `backend/api/comparar.php` | GET `?claves[]=B00001&claves[]=U60001` (1–5 claves) |
| `backend/api/resumen.php` | GET (global stats) or `?institucion=U6` (filtered) |
| `backend/api/planteles.php` | GET `?q=CETIS` (search) or `?clave=B00001` (detail) or empty (all) |
| `backend/etl/carga_csv.php` | CLI-only ETL: `php backend/etl/carga_csv.php --archivo=/path.csv` |
| `backend/etl/carga_planteles.py` | CLI-only ETL: extracts OPC_EDU_2025.pdf → SQL inserts for `planteles` table |
| `database/schema.sql` | DDL — table `sustentantes` (66 cols) + 2 views + table `planteles` (catalog) |
| `.vscode/sftp.json` | SFTP auto-deploy to `192.168.1.81` → `/var/www/html/ecoems/` (plaintext password `debian`) |

## Known issues
- `frontend/js/graficas.js` **missing** but harmless — Chart.js logic is inline on escuela, comparar, resumen; 404 is cosmetic.
- `comparar.php` frontend limits to **4 slots**, backend API accepts **1–5** — extra slot unused.
- No `.gitignore` — sensitive data (temp/ CSVs, SFTP password in `.vscode/sftp.json`) could be committed.

## Architecture
- **No build step, no dev server** — PHP files served directly by Apache/Nginx; frontend → backend via `fetch("../backend/api/...php")`
- Navbar is shared via `frontend/includes/navbar.php` — included by all 7 pages; active link detected dynamically via `basename($_SERVER['PHP_SELF'])`
- Logo is inline SVG in the navbar (`#023047` bg, `#00e5ff` "ECOEMS" text, white subtitle)
- `escuela.php?plantel=XXX` auto-triggers search on load; `?q=` for autocomplete (debounced 350ms)
- `comparar.php` reads `sessionStorage` key `comparar_agregar` (set by `escuela.php`'s FAB)
- `resumen.php` loads data on DOM ready via `cargarResumen()`
- `planteles.php` fetches catalog from `backend/api/planteles.php` — supports `?q=` search or empty (all)
- All pages share `frontend/css/estilos.css` — CSS custom properties palette in `:root`
- ETL is CLI-gated: `php_sapi_name() !== 'cli'` → 403; normalizes CSV encoding (latin1→utf8), batch-commits every 500 rows
- Git origin: `https://github.com/MelSurikun/ecoems-daw-app.git`

## Commands
```bash
# ETL import (run on Debian VM where CSV lives)
php backend/etl/carga_csv.php --archivo=/home/debian/BD_SUSTENTANTES_2024.csv

# ETL planteles from PDF (extract 902 schools → SQL inserts)
python3 backend/etl/carga_planteles.py temp/OPC_EDU_2025.pdf

# Init DB (schema only — planteles catalog included)
mysql -u root < database/schema.sql
```

## Conventions
- No lint, typecheck, test framework, or CI
- DB `ecoems_db`, charset `utf8mb4`; column names are Spanish abbreviations (e.g., `nglobal` = global score)
- API envelope `{status, datos}`; 400 on missing params
