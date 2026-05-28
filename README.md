<div align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="">
    <img alt="ECOEMS" src="" width="480">
  </picture>
  <br><br>

  **Portal de consulta y análisis de resultados del concurso de asignación a la Educación Media Superior en la Zona Metropolitana de la Ciudad de México.**

  <br>

  [![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
  [![MariaDB](https://img.shields.io/badge/MariaDB-11.x-003545?style=flat-square&logo=mariadb&logoColor=white)](https://mariadb.org)
  [![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=flat-square&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
  [![Chart.js](https://img.shields.io/badge/Chart.js-4.4.0-FF6384?style=flat-square&logo=chartdotjs&logoColor=white)](https://www.chartjs.org)
  [![Leaflet](https://img.shields.io/badge/Leaflet-1.9.4-199900?style=flat-square&logo=leaflet&logoColor=white)](https://leafletjs.com)
  [![CSS3](https://img.shields.io/badge/CSS3-Flexbox_Grid-1572B6?style=flat-square&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
  [![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)]()
</div>

---

## 📋 Tabla de Contenido

- [Descripción](#-descripción)
- [Stack Tecnológico](#-stack-tecnológico)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Instalación](#-instalación)
  - [Requisitos](#requisitos)
  - [Base de Datos](#base-de-datos)
  - [Carga de Datos](#carga-de-datos)
- [Uso](#-uso)
- [API REST](#-api-rest)
- [Equipo](#-equipo)
- [Licencia](#-licencia)

---

## 🎯 Descripción

**ECOEMS** (*Estadísticas del Concurso de Opciones Educativas de la Educación Media Superior*) es una aplicación web que permite explorar, comparar y visualizar datos históricos del concurso de asignación escolar COMIPEMS. La plataforma ofrece:

- **Búsqueda** de planteles educativos por nombre o clave.
- **Comparación** lado a lado de hasta 4 escuelas con gráficas de puntajes históricos.
- **Mapa interactivo** con geolocalización de los 902 planteles registrados.
- **Resumen estadístico** con métricas globales y filtros por institución.
- **Catálogo completo** de planteles con búsqueda y filtros por tipo de institución.

---

## 🛠 Stack Tecnológico

| Tecnología | Versión | Propósito |
|---|---|---|
| **PHP** | 8.x | Backend, API REST, templates |
| **MariaDB** | 11.x | Base de datos relacional |
| **JavaScript** | ES6 | Frontend interactivo |
| **Chart.js** | 4.4.0 | Gráficas de datos (CDN) |
| **Leaflet** | 1.9.4 | Mapas interactivos (CDN) |
| **CSS3** | — | Diseño responsive con custom properties |
| **Sora + IBM Plex Serif** | — | Tipografía vía Google Fonts |

Sin build steps, sin npm, sin composer — servido directamente por Apache/Nginx.

---

## 📁 Estructura del Proyecto

```
ecoems-daw-app/
├── frontend/               → Interfaces de usuario
│   ├── index.php           →   Página de inicio (búsqueda)
│   ├── escuela.php         →   Ficha por plantel (stats)
│   ├── comparar.php        →   Comparador (máx. 4 planteles)
│   ├── mapa.php            →   Mapa interactivo (Leaflet)
│   ├── resumen.php         →   Estadísticas generales
│   ├── planteles.php       →   Catálogo completo de planteles
│   ├── acerca.php          →   Acerca del proyecto
│   ├── includes/
│   │   └── navbar.php      →   Navbar compartido (SVG logo)
│   ├── css/
│   │   └── estilos.css     →   Estilos globales
│   └── js/
│       ├── graficas.js     →   Módulos Chart.js
│       └── mapa.js         →   Lógica Leaflet
├── backend/
│   ├── config.php          →   Conexión PDO a MariaDB
│   ├── api/
│   │   ├── escuela.php     →   GET /?plantel=CLAVE&q=texto
│   │   ├── comparar.php    →   GET /?claves[]=A&claves[]=B
│   │   ├── resumen.php     →   GET / (global) / ?institucion=X
│   │   └── planteles.php   →   GET /?q=texto / ?clave=X (vacio=todo)
│   └── etl/
│       ├── carga_csv.php   →   ETL: CSV → tabla sustentantes (CLI)
│       └── carga_planteles.py → ETL: PDF → tabla planteles
├── database/
│   └── schema.sql          →   DDL completo (tablas + vistas)
├── temp/                   →   Archivos temporales (CSV, PDF extraído)
├── docs/                   →   Documentación del proyecto
└── prototipo/              →   Wireframes (primera entrega)
```

---

## 🚀 Instalación

### Requisitos

- PHP 8.x con extensiones `pdo_mysql` y `mbstring`
- MariaDB 10.x / 11.x
- Apache o Nginx
- Python 3 + `pdftotext` (solo para ETL de planteles)

### Base de Datos

```bash
# Crear la base de datos y tablas
mysql -u root < database/schema.sql

# Poblar catálogo de planteles (902 registros)
mysql -u root ecoems_db < temp/planteles_inserts.sql
```

### Carga de Datos (CSV)

```bash
# Importar archivo CSV de sustentantes
php backend/etl/carga_csv.php --archivo=/ruta/al/archivo.csv
```

> **Nota:** El ETL normaliza codificación (latin1 → utf8), valúa columnas y hace commit cada 500 filas. Ejecutar solo en CLI.

### Extracción de Planteles desde PDF

```bash
# Extraer 902 planteles del PDF oficial COMIPEMS
python3 backend/etl/carga_planteles.py temp/OPC_EDU_2025.pdf

# El script genera temp/planteles_inserts.sql automáticamente
```

---

## 💻 Uso

| Ruta | Descripción |
|---|---|
| `/index.php` | Búsqueda por nombre, clave o tipo de institución |
| `/escuela.php?plantel=B00001` | Ficha completa con estadísticas históricas |
| `/comparar.php` | Comparación simultánea de hasta 4 planteles |
| `/mapa.php` | Vista geográfica de todos los planteles |
| `/resumen.php` | Dashboard con métricas globales |
| `/planteles.php` | Catálogo completo con búsqueda y filtros |
| `/acerca.php` | Información del proyecto y equipo |

---

## 🔌 API REST

Todos los endpoints devuelven `{ status, datos }`.

| Endpoint | Parámetros | Descripción |
|---|---|---|
| `backend/api/planteles.php` | `?q=texto` / `?clave=X` / (vacío) | Catálogo de planteles |
| `backend/api/escuela.php` | `?plantel=X` (stats) / `?q=texto` (autocomplete) | Datos por plantel |
| `backend/api/comparar.php` | `?claves[]=A&claves[]=B` (1-5) | Comparación múltiple |
| `backend/api/resumen.php` | `?institucion=X` / (vacío = global) | Estadísticas generales |

---

## 👥 Equipo

| Integrante | Rol |
|---|---|
| **Héctor** | Analista de Datos — métricas, consultas SQL, validación del dataset |
| **Melanie** | Backend & BD — PHP, MariaDB, API, ETL |
| **Amalia** | Frontend — HTML5/CSS3, Chart.js, Leaflet, UX |

Proyecto desarrollado para la materia de **Digitalización de Archivos Web (DAW)** — IPN-LCD.

---

## 📄 Licencia

Este proyecto es educativo y se distribuye bajo licencia - IPN.

---

<div align="center">
  <sub>Hecho con ❤️ por el equipo DAW · IPN 2025-2026</sub>
</div>
