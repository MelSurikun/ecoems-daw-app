# ECOEMS — Portal de Datos DAW

<div align="center">

## Portal web para consulta y análisis de resultados de acceso libre para explorar el histórico de puntajes de corte, demanda y oferta del concurso de asignación a la Educación Media Superior en la Zona Metropolitana de la Ciudad de México.

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

## Equipo de desarrollo

<div align="center">

| Integrante | Rol | Descripción |
|------------|-----|-------------|
| Héctor | Analista de Datos | Exploración y validación del dataset ECOEMS, definición de métricas, consultas SQL y lógica de análisis estadístico. |
| Melanie | Backend & Base de Datos | Desarrollo en PHP, diseño de la base de datos MariaDB, scripts ETL de carga de CSV y endpoints de API. |
| Amalia | Frontend | Diseño e implementación de interfaces HTML5/CSS3, integración de Chart.js para gráficas y Leaflet.js para el mapa. |

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