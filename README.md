# ECOEMS — Portal de Datos COMIPEMS

Portal web para consultar, comparar y visualizar los resultados del concurso COMIPEMS de asignacion a la educacion media superior en la Zona Metropolitana de la Ciudad de Mexico.

---

## 📦 Para descargar el proyecto en tu computadora

Sigue estos pasos si quieres tener la base de datos en tu PC.

### Requisitos

-   **MariaDB** 10+ o MySQL 8+
-   Un cliente de terminal (CMD, PowerShell, Terminal)

---

### Paso 1 — Instalar MariaDB

1.  Ve a [mariadb.org/download](https://mariadb.org/download/)
2.  Elige tu sistema operativo y descarga el instalador
3.  Durante la instalacion **anota la contrasena de root** que elegiste
4.  Al terminar, abre una terminal y verifica que quedo bien instalado:

    ```bash
    mysql --version
    ```

    Deberias ver algo como `mysql  Ver 15.1 Distrib 10.11.x-MariaDB`.

---

### Paso 2 — Descargar el proyecto

Clona el repositorio o descarga el ZIP y extraelo en tu computadora.

---

### Paso 3 — Importar la base de datos

Abre una terminal en la carpeta del proyecto y ejecuta:

```bash
mysql -u root -p < database/ecoems_db.sql
```

> **Nota:** Te pedira la contrasena que elegiste al instalar MariaDB.

Este comando crea la base `ecoems_db`, las tablas, las vistas y carga los 983 planteles del catalogo COMIPEMS.

---

### Paso 4 — Ver las paginas web

Los archivos `.php` **no se abren directo en el navegador** (como un `.html`). Necesitan un servidor web.

El proyecto esta disenado para correr en el servidor Apache de la maquina virtual del equipo. Pide a un integrante la direccion IP para acceder desde tu navegador.

---

## 🗄️ Acerca de la base de datos

La base `ecoems_db` tiene **2 tablas** y **2 vistas**:

| Objeto | Tipo | Contenido |
|---|---|---|
| `sustentantes` | Tabla | 66 columnas con datos demograficos, resultados del examen COMIPEMS y asignacion de cada aspirante |
| `planteles` | Tabla | Catalogo de 983 opciones educativas con clave, nombre, especialidad, subsistema, ubicacion y coordenadas |
| `v_corte_por_plantel` | Vista | Puntajes de corte por plantel |
| `v_resumen_instituciones` | Vista | Metricas agregadas por institucion |

```
ecoems_db
├── sustentantes          (tabla principal ~360 000 registros)
│   ├── folio, sexo, promedio...
│   ├── opc_ed01 … opc_ed20    (opciones solicitadas)
│   ├── nglobal, nhv, nmat…    (aciertos por area)
│   └── expl_asi, asig_fin     (asignacion)
├── planteles             (983 opciones educativas)
│   ├── clave, nombre, especialidad
│   ├── subsistema, municipio, estado
│   └── latitud, longitud
├── v_corte_por_plantel   (puntaje de corte por clave)
└── v_resumen_instituciones (totales por institucion)
```

---

## ⚙️ Stack tecnologico

| Tecnologia | Uso |
|---|---|
| PHP 8 | Backend, API REST, templates |
| MariaDB | Base de datos relacional |
| JavaScript | Frontend interactivo |
| Chart.js 4.4.0 | Graficas (CDN) |
| Leaflet 1.9.4 | Mapas interactivos (CDN) |
| CSS3 | Estilos responsive |
| Sora + IBM Plex Serif | Tipografia (Google Fonts) |

> Sin build steps, sin npm, sin composer. Servido directamente por Apache/Nginx.

---

## 🛠️ Instalacion para desarrollo

### Requisitos

-   PHP 8 con extensiones `pdo_mysql` y `mbstring`
-   MariaDB 10+ o MySQL 8+
-   Apache o Nginx
-   Python 3 + `pdftotext` (solo para ETL de planteles)

### 1. Base de datos

```bash
mysql -u root < database/schema.sql
```

### 2. Catalogos

```bash
# Poblar los 983 planteles (extraidos del PDF oficial COMIPEMS)
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

Genera `temp/planteles_inserts.sql` con los 983 planteles.

---

## 📁 Estructura del proyecto

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
│   ├── schema.sql     → DDL completo
│   └── ecoems_db.sql  → Schema + datos (todo en uno)
├── temp/              → Archivos temporales (CSV, SQL generado)
├── docs/
└── prototipo/
```

---

## 🌐 Paginas del portal

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

## 🔌 API REST

Todos los endpoints devuelven `{ status, datos }`.

| Endpoint | Parametros | Descripcion |
|---|---|---|
| `backend/api/planteles.php` | `?q=texto` / `?clave=X` / vacio | Catalogo de planteles |
| `backend/api/escuela.php` | `?plantel=X` / `?q=texto` | Datos por plantel |
| `backend/api/comparar.php` | `?claves[]=A&claves[]=B` (1-5) | Comparacion multiple |
| `backend/api/resumen.php` | `?institucion=X` / vacio | Estadisticas generales |

---

## 🗄️ Configuracion de la base de datos

Editar `backend/config.php` con las credenciales de tu base de datos:

```php
$host = 'localhost';
$db   = 'ecoems_db';
$user = 'ecoems_user';
$pass = 'password';
```

---

## 👥 Equipo

-   **Hector** — Analisis de datos, metricas, consultas SQL
-   **Melanie** — Backend, base de datos, API, ETL
-   **Amalia** — Frontend, diseno, Chart.js, Leaflet

Proyecto para la materia de **Desarrollo de Aplicaciones Web (DAW)** — IPN-LCD.
