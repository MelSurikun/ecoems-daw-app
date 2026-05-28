-- ============================================================
--  ECOEMS — schema.sql
--  DDL MariaDB — Portal COMIPEMS/ECOEMS 2024
--  Basado en BD_SUSTENTANTES_2024 (CENEVAL)
-- ============================================================

CREATE DATABASE IF NOT EXISTS ecoems_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ecoems_db;

-- ------------------------------------------------------------
-- Tabla principal: un registro por aspirante
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sustentantes (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Identificación
    folio         VARCHAR(9)   NOT NULL UNIQUE  COMMENT 'Folio de registro',
    sexo          CHAR(1)                       COMMENT 'H=Hombre, M=Mujer',

    -- Domicilio del aspirante
    colonia       VARCHAR(30),
    cp            CHAR(5),
    cve_alcmun    CHAR(3)                       COMMENT 'Clave alcaldía/municipio domicilio',
    cve_ent       CHAR(2)                       COMMENT 'Clave entidad domicilio',
    alcmun_asp    VARCHAR(36)                   COMMENT 'Nombre alcaldía/municipio domicilio',

    -- Perfil académico previo
    catego_asp    CHAR(1)                       COMMENT 'E=Egresado, F=Foráneo, I=INEA, Z=Local',
    cct           VARCHAR(10)                   COMMENT 'CCT escuela secundaria de procedencia',
    regi_sec      VARCHAR(20)                   COMMENT 'Régimen secundaria origen',
    moda_sec      VARCHAR(30)                   COMMENT 'Modalidad secundaria origen',
    cve_munalc    CHAR(3)                       COMMENT 'Clave municipio de la escuela',
    munalc_esc    VARCHAR(36)                   COMMENT 'Nombre municipio de la escuela',
    promedio      DECIMAL(4,1)                  COMMENT 'Promedio certificado secundaria',

    -- Opciones educativas solicitadas (hasta 20)
    opc_ed01      CHAR(7), opc_ed02 CHAR(7), opc_ed03 CHAR(7), opc_ed04 CHAR(7),
    opc_ed05      CHAR(7), opc_ed06 CHAR(7), opc_ed07 CHAR(7), opc_ed08 CHAR(7),
    opc_ed09      CHAR(7), opc_ed10 CHAR(7), opc_ed11 CHAR(7), opc_ed12 CHAR(7),
    opc_ed13      CHAR(7), opc_ed14 CHAR(7), opc_ed15 CHAR(7), opc_ed16 CHAR(7),
    opc_ed17      CHAR(7), opc_ed18 CHAR(7), opc_ed19 CHAR(7), opc_ed20 CHAR(7),

    -- Examen
    fturn_exam    CHAR(1)                       COMMENT 'Turno de aplicación 1-8',
    pre_exa       CHAR(1)                       COMMENT 'S=Sí presentó, N=No',
    examen        CHAR(2)                       COMMENT 'CS/CN=CENEVAL, US/UN=UNAM',

    -- Resultados: aciertos (número)
    nglobal       TINYINT UNSIGNED              COMMENT 'Aciertos global (0-128)',
    nhv           TINYINT UNSIGNED              COMMENT 'Habilidad verbal (0-16)',
    nesp          TINYINT UNSIGNED              COMMENT 'Español (0-12)',
    nhis          TINYINT UNSIGNED              COMMENT 'Historia (0-12)',
    ngeo          TINYINT UNSIGNED              COMMENT 'Geografía (0-12)',
    nfce          TINYINT UNSIGNED              COMMENT 'Form. cívica y ética (0-12)',
    nhm           TINYINT UNSIGNED              COMMENT 'Habilidad matemática (0-16)',
    nmat          TINYINT UNSIGNED              COMMENT 'Matemáticas (0-12)',
    nfis          TINYINT UNSIGNED              COMMENT 'Física (0-12)',
    nqui          TINYINT UNSIGNED              COMMENT 'Química (0-12)',
    nbio          TINYINT UNSIGNED              COMMENT 'Biología (0-12)',

    -- Resultados: porcentajes
    pnglobal      DECIMAL(5,2)                  COMMENT '% aciertos global',
    pnhv          DECIMAL(5,2),
    pnesp         DECIMAL(5,2),
    pnhis         DECIMAL(5,2),
    pngeo         DECIMAL(5,2),
    pnfce         DECIMAL(5,2),
    pnhm          DECIMAL(5,2),
    pnmat         DECIMAL(5,2),
    pnfis         DECIMAL(5,2),
    pnqui         DECIMAL(5,2),
    pnbio         DECIMAL(5,2),

    -- Asignación inicial (CENEVAL)
    expl_asi      CHAR(3)                       COMMENT 'ASI/CDO/NP/SC/BI',
    nopc_asi      TINYINT UNSIGNED              COMMENT 'Número de opción asignada',
    copc_asi      CHAR(7)                       COMMENT 'Clave opción asignada',
    cveins_asi    CHAR(2)                       COMMENT 'Clave institución asignada',
    cvesub_asi    CHAR(2)                       COMMENT 'Clave subsistema asignado',

    -- Asignación final (tras CDO y SC)
    asig_fin      CHAR(7),
    expl_fin      VARCHAR(10)                   COMMENT 'ASI/PREINS/REASIG/BI/CDO/NP/SC',
    nopc_fin      TINYINT UNSIGNED,
    inst_fin      CHAR(2),

    anio          YEAR DEFAULT 2024             COMMENT 'Año del proceso'

) ENGINE=InnoDB COMMENT='Sustentantes COMIPEMS 2024';

-- ------------------------------------------------------------
-- Catálogo de planteles (opciones educativas)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS planteles (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clave        CHAR(7)      NOT NULL UNIQUE COMMENT 'Clave COMIPEMS (ej. B00001, U60001)',
    nombre       VARCHAR(120) NOT NULL COMMENT 'Nombre del plantel',
    especialidad VARCHAR(100) COMMENT 'Especialidad / carrera',
    subsistema   VARCHAR(50)  COMMENT 'DGETI, UNAM, IPN, CONALEP, COLBACH, etc.',
    turno        VARCHAR(20)  COMMENT 'Matutino, Vespertino',
    municipio    VARCHAR(50),
    estado       VARCHAR(30)  DEFAULT 'Ciudad de México',
    direccion    VARCHAR(250) COMMENT 'Domicilio del plantel',
    latitud      DECIMAL(9,6),
    longitud     DECIMAL(9,6)
) ENGINE=InnoDB COMMENT='Catálogo de opciones educativas COMIPEMS';

-- Índices para búsquedas frecuentes del portal
CREATE INDEX idx_alcmun_asp   ON sustentantes (alcmun_asp);
CREATE INDEX idx_cveins_asi   ON sustentantes (cveins_asi);
CREATE INDEX idx_cvesub_asi   ON sustentantes (cvesub_asi);
CREATE INDEX idx_expl_fin     ON sustentantes (expl_fin);
CREATE INDEX idx_nglobal      ON sustentantes (nglobal);
CREATE INDEX idx_opc_ed01     ON sustentantes (opc_ed01);

-- ------------------------------------------------------------
-- Vista útil: resumen por institución asignada
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW v_resumen_instituciones AS
SELECT
    cveins_asi                          AS clave_inst,
    COUNT(*)                            AS total_aspirantes,
    SUM(expl_fin = 'ASI')               AS asignados,
    ROUND(AVG(nglobal), 1)              AS promedio_aciertos,
    ROUND(AVG(promedio), 1)             AS promedio_certificado,
    SUM(sexo = 'H')                     AS hombres,
    SUM(sexo = 'M')                     AS mujeres
FROM sustentantes
WHERE pre_exa = 'S'
GROUP BY cveins_asi;

-- ------------------------------------------------------------
-- Vista: puntaje de corte por opción educativa (plantel)
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW v_corte_por_plantel AS
SELECT
    opc_ed01                            AS clave_plantel,
    COUNT(*)                            AS total_solicitudes,
    MIN(CASE WHEN expl_fin='ASI' THEN nglobal END) AS puntaje_corte_min,
    MAX(CASE WHEN expl_fin='ASI' THEN nglobal END) AS puntaje_corte_max,
    ROUND(AVG(CASE WHEN expl_fin='ASI' THEN nglobal END), 1) AS puntaje_corte_prom
FROM sustentantes
WHERE pre_exa = 'S' AND opc_ed01 IS NOT NULL
GROUP BY opc_ed01;
