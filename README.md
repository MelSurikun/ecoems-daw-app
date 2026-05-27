# ECOEMS — Portal de Datos DAW

<div align="center">

## Portal web para consulta y análisis de resultados de la Evaluación de Competencias de Egresados de Educación Media Superior (ECOEMS)

---

### Stack Tecnológico

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-003545?style=for-the-badge&logo=mariadb&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Chart.js](https://img.shields.io/badge/Chart.js-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white)
![Leaflet](https://img.shields.io/badge/Leaflet-199900?style=for-the-badge&logo=leaflet&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

</div>

---

## Estructura del Proyecto

```bash
ecoems-daw-app/
├── frontend/           → Interfaces de usuario (PHP + CSS + JS)
│   ├── index.php       → Página de inicio
│   ├── escuela.php     → Búsqueda por plantel
│   ├── comparar.php    → Comparación de planteles
│   ├── mapa.php        → Mapa interactivo
│   ├── resumen.php     → Estadísticas generales
│   ├── acerca.php      → Acerca del proyecto
│   ├── css/estilos.css
│   └── js/
│       ├── graficas.js  → Chart.js
│       └── mapa.js      → Leaflet.js
├── backend/
│   ├── config.php       → Conexión a MariaDB
│   ├── api/
│   │   ├── escuela.php  → GET búsqueda por plantel
│   │   ├── comparar.php → GET comparación múltiple
│   │   └── resumen.php  → GET estadísticas generales
│   └── etl/
│       └── carga_csv.php → Importación CSV → BD (CLI)
├── database/
│   ├── schema.sql        → DDL MariaDB
│   └── datos_muestra.sql → Datos de prueba
├── prototipo/            → Wireframes estáticos (Primera Entrega)
└── docs/                 → Documentación PDF