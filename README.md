# ECOEMS — Portal de Datos DAW

Portal web para consulta, comparacion y visualizacion de resultados del concurso COMIPEMS de asignacion a la educacion media superior en la Zona Metropolitana de la Ciudad de Mexico.

---

## Stack

| Tecnologia | Uso |
|---|---|
| PHP 8 | Backend, API REST, templates |
| MariaDB | Base de datos relacional |
| JavaScript | Frontend interactivo |
| Chart.js 4.4.0 | Graficas (CDN) |
| Leaflet 1.9.4 | Mapas interactivos (CDN) |
| CSS3 | Estilos responsive |
| Sora + IBM Plex Serif | Tipografia (Google Fonts) |

Sin build steps, sin npm, sin composer. Servido directamente por Apache/Nginx.

---

## Instalacion

### Requisitos

- PHP 8 con extensiones `pdo_mysql` y `mbstring`
- MariaDB 10+ o MySQL 8+
- Apache o Nginx
- Python 3 + `pdftotext` (solo para ETL de planteles)

### 1. Base de datos

```bash
mysql -u root < database/schema.sql
```

### 2. Catalogos

```bash
# Poblar los 902 planteles (extraidos del PDF oficial COMIPEMS)
mysql -u root ecoems_db < temp/planteles_inserts.sql
```

### 3. Carga de datos (CSV)

```bash
php backend/etl/carga_csv.php --archivo=/ruta/al/archivo.csv
```

El ETL normaliza codificacion (latin1 a utf8), valida columnas y hace commit cada 500 filas. Solo se ejecuta en CLI.

### 4. (Opcional) Extraer planteles desde PDF

```bash
python3 backend/etl/carga_planteles.py temp/OPC_EDU_2025.pdf
```

Genera `temp/planteles_inserts.sql` con los 902 planteles.

---

## Estructura

```
ecoems-daw-app/
├── frontend/          → Paginas PHP (index, escuela, comparar, mapa, resumen, planteles, acerca)
│   ├── includes/
│   │   └── navbar.php → Barra de navegacion compartida
│   ├── css/estilos.css
│   └── js/
│       ├── graficas.js
│       └── mapa.js
├── backend/
│   ├── config.php     → Conexion PDO a MariaDB
│   ├── api/           → Endpoints REST (escuela, comparar, resumen, planteles)
│   └── etl/           → Scripts de carga (carga_csv.php, carga_planteles.py)
├── database/
│   └── schema.sql     → DDL completo
├── temp/              → Archivos temporales (CSV, SQL generado)
├── docs/
└── prototipo/
```

---

## Paginas

| Ruta | Que hace |
|---|---|
| `/index.php` | Busqueda por nombre, clave o tipo de institucion |
| `/escuela.php?plantel=CLAVE` | Ficha completa con estadisticas |
| `/comparar.php` | Comparacion de hasta 4 planteles |
| `/mapa.php` | Mapa interactivo con todos los planteles |
| `/resumen.php` | Dashboard con metricas globales |
| `/planteles.php` | Catalogo completo con busqueda y filtros |
| `/acerca.php` | Informacion del proyecto |

---

## API REST

Todos los endpoints devuelven `{ status, datos }`.

| Endpoint | Parametros | Descripcion |
|---|---|---|
| `backend/api/planteles.php` | `?q=texto` / `?clave=X` / vacio | Catalogo de planteles |
| `backend/api/escuela.php` | `?plantel=X` / `?q=texto` | Datos por plantel |
| `backend/api/comparar.php` | `?claves[]=A&claves[]=B` (1-5) | Comparacion multiple |
| `backend/api/resumen.php` | `?institucion=X` / vacio | Estadisticas generales |

---

## Base de datos

El schema crea la base `ecoems_db` con tres objetos principales:

- **sustentantes** — Tabla principal con 66 columnas (folio, datos demograficos, resultados del examen, asignacion)
- **planteles** — Catalogo de 902 opciones educativas con clave, nombre, subsistema, ubicacion y coordenadas
- **v_corte_por_plantel** — Vista con puntajes de corte por plantel
- **v_resumen_instituciones** — Vista con resumen por institucion

### Configuracion

Editar `backend/config.php` con las credenciales de tu base de datos:

```php
$host = 'localhost';
$db   = 'ecoems_db';
$user = 'ecoems_user';
$pass = 'password';
```

---

## Equipo

- **Hector** — Analisis de datos, metricas, consultas SQL
- **Melanie** — Backend, base de datos, API, ETL
- **Amalia** — Frontend, diseno, Chart.js, Leaflet

Proyecto para la materia de Desarrollo de Aplicaciones Web (DAW) — IPN-LCD.
