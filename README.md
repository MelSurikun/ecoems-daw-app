# ECOEMS — Portal de consulta COMIPEMS

Portal web para explorar, comparar y visualizar los resultados del concurso COMIPEMS de asignacion a la educacion media superior en la Zona Metropolitana de la Ciudad de Mexico.

`PHP 8` `MariaDB` `JavaScript vanilla` `Chart.js 4.4` `Leaflet 1.9`
Sin build tools, sin npm, sin composer. Servido directamente por Apache.

· · ·

## Paginas del portal

| Ruta | Que hace |
|---|---|
| `/index.php` | Busqueda por nombre, clave o tipo de institucion |
| `/escuela.php?plantel=CLAVE` | Ficha completa con estadisticas por plantel |
| `/comparar.php` | Comparacion de hasta 4 planteles lado a lado |
| `/mapa.php` | Mapa interactivo con todos los planteles |
| `/resumen.php` | Dashboard con metricas globales |
| `/planteles.php` | Catalogo completo con busqueda y filtros |
| `/acerca.php` | Informacion del proyecto |
| `/login.php` | Inicio de sesion (aspirante / admin) |
| `/registro.php` | Registro de cuenta nueva |
| `/biblioteca.php` | Recursos de estudio (guias, libros, enlaces) |
| `/simulador.php` | Examen simulado tipo COMIPEMS |
| `/dashboard.php` | Panel del aspirante con historial de intentos |
| `/admin/dashboard.php` | Panel administrativo |
| `/admin/examen.php` | Gestion de reactivos del simulador |
| `/admin/planteles.php` | Administracion de catalogo de planteles |
| `/admin/simulador.php` | Estadisticas globales del simulador |
| `/admin/usuarios.php` | Gestion de usuarios |

· · ·

## API REST

Todos los endpoints devuelven `{ status, datos }`, usan prepared statements e incluyen `Access-Control-Allow-Origin: *`.

| Endpoint | Metodo | Auth | Parametros |
|---|---|---|---|
| `backend/api/planteles.php` | GET | — | `?q=texto`, `?clave=X`, vacio = todos |
| `backend/api/escuela.php` | GET | — | `?plantel=X`, `?q=texto` |
| `backend/api/comparar.php` | GET | — | `?claves[]=A&claves[]=B` (1 a 5) |
| `backend/api/resumen.php` | GET | — | `?institucion=X`, vacio = general |
| `backend/api/reactivos.php` | GET | — | `?examen_id=1` — reactivos publicos |
| `backend/api/simulador.php` | GET/POST | requerida | POST guarda intento, GET historial |
| `backend/api/recursos.php` | GET/POST/PUT/DELETE | escritura: admin | CRUD de recursos de estudio |
| `backend/api/metas.php` | GET/PUT | requerida | Opciones de interés y puntaje meta del aspirante |
| `backend/api/auth/login.php` | POST | — | Inicio de sesion (JSON body) |
| `backend/api/auth/logout.php` | POST | — | Cierra sesion |
| `backend/api/auth/registro.php` | POST | — | Registro de aspirante |
| `backend/api/admin/reactivos.php` | GET/POST/PUT/DELETE | admin | CRUD de reactivos |
| `backend/api/admin/simulador_stats.php` | GET | admin | Estadisticas agregadas |
| `backend/api/admin/usuarios.php` | GET/PUT | admin | Listar y actualizar usuarios |
| `backend/api/admin/usuarios_detalle.php` | GET | admin | Detalle de un usuario |

· · ·

## Stack tecnologico

| Tecnologia | Uso |
|---|---|
| PHP 8 | Backend, API REST, templates |
| MariaDB 10+ / MySQL 8+ | Base de datos relacional |
| JavaScript vanilla | Frontend interactivo |
| Chart.js 4.4.0 | Graficas (CDN) |
| Leaflet 1.9.4 | Mapas interactivos (CDN) |
| CSS3 | Estilos responsive con propiedades personalizadas |
| Sora + IBM Plex Serif | Tipografia (Google Fonts) |

· · ·

## Inicio rapido — tener el proyecto en tu maquina

### Requisitos

- MariaDB 10+ o MySQL 8+
- Un cliente de terminal

### Instalar MariaDB

Descarga el instalador desde [mariadb.org/download](https://mariadb.org/download/) y durante la instalacion anota la contrasena de root. Al terminar, verifica:

```bash
mysql --version
```

### Descargar el proyecto

Clona el repositorio o descarga el ZIP y extraelo en tu computadora.

### Importar la base de datos

En la terminal, dentro de la carpeta del proyecto:

```bash
mysql -u root -p < database/ecoems_db.sql
```

Esto crea la base `ecoems_db`, las tablas y las vistas. Despues aplica los modulos y el catalogo curado de planteles:

```bash
mysql -u root -p ecoems_db < database/auth.sql
mysql -u root -p ecoems_db < database/recursos.sql
mysql -u root -p ecoems_db < database/simulador.sql
mysql -u root -p ecoems_db < database/metas.sql
mysql -u root -p ecoems_db < database/planteles_cdmx.sql
mysql -u root -p ecoems_db < database/sustentantes_demo.sql
```

> **Importante:** `planteles_cdmx.sql` y `sustentantes_demo.sql` reemplazan (TRUNCATE) los datos que trae `ecoems_db.sql`. El catalogo activo tiene ~40 planteles solo de CDMX con UNAM e IPN completos.
>
> `auth.sql` crea dos cuentas de prueba: `admin@ecoems.mx` / `Admin123!` (admin) y `aspirante@ecoems.mx` / `Aspirante123!` (aspirante).

### Ver las paginas

Los archivos `.php` necesitan un servidor web — no se abren directo como `.html`. El proyecto corre en Apache. Pide a un integrante la direccion IP para acceder desde el navegador.

· · ·

## Instalacion para desarrollo

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
mysql -u root ecoems_db < temp/planteles_inserts.sql
```

### 3. Carga de datos desde CSV

```bash
php backend/etl/carga_csv.php --archivo=/ruta/al/archivo.csv
```

El ETL normaliza codificacion (latin1 a utf8), valida columnas y hace commit cada 500 filas. Solo se ejecuta en CLI.

### 4. Extraer planteles desde PDF (opcional)

```bash
python3 backend/etl/carga_planteles.py temp/OPC_EDU_2025.pdf
```

Genera `temp/planteles_inserts.sql` con los 983 planteles del PDF oficial.

· · ·

## Base de datos

La base `ecoems_db` tiene **8 tablas** y **2 vistas**:

| Objeto | Tipo | Contenido |
|---|---|---|
| `sustentantes` | Tabla | 66 columnas con datos demograficos, resultados del examen y asignacion de cada aspirante |
| `planteles` | Tabla | Catalogo curado de ~40 planteles CDMX con clave, nombre, subsistema, ubicacion y coordenadas |
| `usuarios` | Tabla | Cuentas de aspirante y administrador con autenticacion por password_hash |
| `recursos` | Tabla | Catalogo de guias, libros y enlaces de estudio por materia |
| `intentos_simulador` | Tabla | Historial de intentos del examen simulado por usuario |
| `examenes` | Tabla | Versiones del examen simulador |
| `reactivos` | Tabla | Preguntas del simulador con opciones, respuesta y figuras asociadas |
| `figuras` | Tabla | Imagenes en base64 para los reactivos |
| `v_corte_por_plantel` | Vista | Puntajes de corte por clave de plantel |
| `v_resumen_instituciones` | Vista | Metricas agregadas por institucion |

```
ecoems_db
├── sustentantes
│   ├── folio, sexo, promedio ...
│   ├── opc_ed01 ... opc_ed20   (opciones solicitadas)
│   ├── nglobal, nhv, nmat ...  (aciertos por area)
│   └── expl_asi, asig_fin      (asignacion)
├── planteles
│   ├── clave, nombre, especialidad
│   ├── subsistema, municipio, estado
│   └── latitud, longitud
├── usuarios
├── recursos
├── intentos_simulador
├── examenes
├── reactivos
├── figuras
├── v_corte_por_plantel         (puntaje de corte por clave)
└── v_resumen_instituciones     (totales por institucion)
```

> Preferir las vistas `v_corte_por_plantel` y `v_resumen_instituciones` para obtener estadisticas en lugar de re-agregar `sustentantes` directamente.

· · ·

## Estructura del proyecto

```
ecoems-daw-app/
├── frontend/
│   ├── includes/
│   │   ├── navbar.php          — Navbar publico / aspirante
│   │   └── navbar_admin.php    — Navbar para administradores
│   ├── admin/                  — Paneles administrativos
│   ├── css/estilos.css         — Estilos con variables CSS (--bordo, --font-display)
│   ├── js/
│   │   ├── graficas.js         — Chart.js
│   │   └── mapa.js             — Leaflet (claves: U6=UNAM, I5=IPN, B0=COLBACH...)
│   ├── index, escuela, comparar, mapa, resumen...
│   ├── login, registro
│   ├── biblioteca, simulador, dashboard
│   └── acerca.php
├── backend/
│   ├── config.php              — Conexion PDO a MariaDB (memoizada)
│   ├── auth.php                — Helpers de sesion (requiereSesion, requiereRol)
│   ├── api/
│   │   ├── auth/               — login, logout, registro
│   │   ├── admin/              — reactivos, simulador_stats, usuarios
│   │   ├── planteles, escuela, comparar, resumen
│   │   ├── reactivos, simulador, recursos
│   │   └── ...
│   └── etl/                    — carga_csv.php, carga_planteles.py
├── database/
│   ├── schema.sql              — DDL completo (8 tablas + 2 vistas)
│   ├── ecoems_db.sql           — Schema + datos legacy
│   ├── auth.sql                — Tabla usuarios + cuentas semilla
│   ├── recursos.sql            — Datos de biblioteca
│   ├── simulador.sql           — Tablas del simulador
│   ├── metas.sql               — Opciones de interés y puntaje meta del aspirante
│   ├── planteles_cdmx.sql      — 40 planteles CDMX (UNAM + IPN completos)
│   ├── sustentantes_demo.sql   — 150 sustentantes por plantel
│   └── update_coords.sql
├── temp/                       — PDF oficial, CSV, SQL generado por ETL
└── docs/
```

· · ·

## Configuracion

Editar `backend/config.php` con las credenciales de la base de datos:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecoems_db');
define('DB_USER', 'ecoems_user');
define('DB_PASS', 'password');
```

· · ·

## Equipo

| Integrante | Rol |
|---|---|
| Hector | Analisis de datos, metricas, consultas SQL |
| Melanie | Backend, base de datos, API, ETL |
| Amalia | Frontend, diseno, Chart.js, Leaflet |

Proyecto para la materia de **Desarrollo de Aplicaciones Web (DAW)** — IPN-LCD.
