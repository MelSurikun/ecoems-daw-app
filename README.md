# ECOEMS — Portal de Datos DAW

<div align="center">

## Plataforma Web de Consulta y Análisis de Resultados ECOEMS

Sistema desarrollado para visualizar, consultar y analizar resultados de la Evaluación de Competencias de Egresados de Educación Media Superior (ECOEMS), permitiendo explorar estadísticas por plantel, comparaciones y visualización geográfica.

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

## Descripción del Proyecto

ECOEMS — Portal de Datos DAW es una aplicación web académica enfocada en la consulta y análisis de resultados de la Evaluación de Competencias de Egresados de Educación Media Superior.

El sistema permite realizar búsquedas por plantel, comparar resultados entre escuelas, visualizar estadísticas generales y explorar información mediante mapas interactivos y gráficas dinámicas.

---

## Equipo

<div align="center">

| Nombre | Rol |
|--------|-----|
| Melanie Hernández López | Desarrollo Frontend & Diseño UI |
| Ángel David Reyes Calva | Backend & API |
| Byron Leonardo Ayala Velasco | Base de Datos & ETL |
| Leo Galvan Landan | Visualización de Datos & Documentación |

</div>

---

## Objetivos

1. Desarrollar una plataforma web para consulta de resultados ECOEMS
2. Implementar integración entre frontend, backend y base de datos
3. Visualizar estadísticas mediante gráficas dinámicas y mapas interactivos
4. Facilitar la comparación de planteles educativos
5. Aplicar buenas prácticas de desarrollo web y organización modular

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
│       ├── graficas.js → Chart.js
│       └── mapa.js     → Leaflet.js
├── backend/
│   ├── config.php      → Conexión a MariaDB
│   ├── api/
│   │   ├── escuela.php
│   │   ├── comparar.php
│   │   └── resumen.php
│   └── etl/
│       └── carga_csv.php
├── database/
│   ├── schema.sql
│   └── datos_muestra.sql
├── prototipo/
└── docs/