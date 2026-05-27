-- ============================================================
--  ECOEMS — datos_muestra.sql
--  INSERT de muestra para desarrollo y pruebas
-- ============================================================

USE ecoems_db;

-- Planteles de ejemplo
INSERT IGNORE INTO planteles (cct, nombre, subsistema, municipio, estado, latitud, longitud) VALUES
('09DEM0001X', 'CETIS 1',           'DGETI',   'Cuauhtémoc',       'Ciudad de México', 19.4284, -99.1277),
('09DEM0002Y', 'CBTIS 1',           'DGETI',   'Iztapalapa',       'Ciudad de México', 19.3571, -99.0745),
('15DCC0001Z', 'CECYT 1 Granadas',  'IPN',     'Miguel Hidalgo',   'Ciudad de México', 19.4326, -99.1897),
('15DCC0002A', 'Prepa 1 UNAM',      'UNAM',    'Coyoacán',         'Ciudad de México', 19.3258, -99.1685),
('19DEM0010B', 'CONALEP Monterrey', 'CONALEP', 'Monterrey',        'Nuevo León',       25.6714, -100.3090);

-- Resultados de muestra (ciclo 2023-2024, Matemáticas)
INSERT IGNORE INTO resultados (plantel_id, ciclo_escolar, pct_suficiencia, pct_competente, pct_destacado, pct_insuficiente, total_alumnos, materia)
SELECT id, '2023-2024', 35.2, 28.4, 12.1, 24.3, 320, 'Matemáticas'
FROM planteles WHERE cct = '09DEM0001X';

INSERT IGNORE INTO resultados (plantel_id, ciclo_escolar, pct_suficiencia, pct_competente, pct_destacado, pct_insuficiente, total_alumnos, materia)
SELECT id, '2023-2024', 30.5, 25.0, 8.7, 35.8, 450, 'Matemáticas'
FROM planteles WHERE cct = '09DEM0002Y';

INSERT IGNORE INTO resultados (plantel_id, ciclo_escolar, pct_suficiencia, pct_competente, pct_destacado, pct_insuficiente, total_alumnos, materia)
SELECT id, '2023-2024', 41.0, 32.5, 18.2, 8.3, 280, 'Matemáticas'
FROM planteles WHERE cct = '15DCC0001Z';
