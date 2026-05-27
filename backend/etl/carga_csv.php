<?php
// ============================================================
//  ECOEMS — etl/carga_csv.php
//  Importación: BD_SUSTENTANTES_2024.csv → MariaDB
//
//  Uso (en tu VM Debian):
//    php carga_csv.php --archivo=/home/debian/BD_SUSTENTANTES_2024.csv
//
//  Tiempo estimado: 2-5 min dependiendo del tamaño del CSV
// ============================================================

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("Solo uso CLI\n");
}

require_once __DIR__ . '/../config.php';

// --- Parsear argumento --archivo ---
$opts = getopt('', ['archivo:']);
$archivo = $opts['archivo'] ?? null;

if (!$archivo || !file_exists($archivo)) {
    echo "ERROR: Debes pasar --archivo=<ruta_al_csv>\n";
    echo "Ejemplo: php carga_csv.php --archivo=/home/debian/BD_SUSTENTANTES_2024.csv\n";
    exit(1);
}

echo "=== ETL ECOEMS — Inicio: " . date('H:i:s') . " ===\n";
echo "Archivo: $archivo\n\n";

$pdo = getDB();

// Columnas del CSV en el orden del diccionario de datos
$columnas = [
    'folio','sexo','colonia','cp','cve_alcmun','cve_ent','alcmun_asp',
    'catego_asp','cct','regi_sec','moda_sec','cve_munalc','munalc_esc','promedio',
    'opc_ed01','opc_ed02','opc_ed03','opc_ed04','opc_ed05','opc_ed06','opc_ed07',
    'opc_ed08','opc_ed09','opc_ed10','opc_ed11','opc_ed12','opc_ed13','opc_ed14',
    'opc_ed15','opc_ed16','opc_ed17','opc_ed18','opc_ed19','opc_ed20',
    'fturn_exam','pre_exa','examen',
    'nglobal','nhv','nesp','nhis','ngeo','nfce','nhm','nmat','nfis','nqui','nbio',
    'pnglobal','pnhv','pnesp','pnhis','pngeo','pnfce','pnhm','pnmat','pnfis','pnqui','pnbio',
    'expl_asi','nopc_asi','copc_asi','cveins_asi','cvesub_asi',
    'asig_fin','expl_fin','nopc_fin','inst_fin'
];

// Preparar INSERT
$placeholders = implode(',', array_fill(0, count($columnas), '?'));
$cols         = implode(',', $columnas);
$sql = "INSERT IGNORE INTO sustentantes ($cols) VALUES ($placeholders)";
$stmt = $pdo->prepare($sql);

// Leer CSV
$handle    = fopen($archivo, 'r');
$encabezado = fgetcsv($handle); // saltar primera fila (encabezados)

$insertados = 0;
$errores    = 0;
$linea      = 1;

// Usar transacciones en lotes de 500 para velocidad
$pdo->beginTransaction();

while (($fila = fgetcsv($handle)) !== false) {
    $linea++;

    // Normalizar encoding latin-1 → utf-8 si es necesario
    $fila = array_map(function($v) {
        $enc = mb_detect_encoding($v, ['UTF-8','ISO-8859-1','Windows-1252'], true);
        return ($enc && $enc !== 'UTF-8') ? mb_convert_encoding($v, 'UTF-8', $enc) : $v;
    }, $fila);

    // Reemplazar cadenas vacías con NULL
    $fila = array_map(fn($v) => ($v === '' ? null : $v), $fila);

    // Ajustar al número exacto de columnas esperadas
    $fila = array_slice(array_pad($fila, count($columnas), null), 0, count($columnas));

    try {
        $stmt->execute($fila);
        $insertados++;
    } catch (\PDOException $e) {
        $errores++;
        if ($errores <= 5) {
            echo "  Error línea $linea: " . $e->getMessage() . "\n";
        }
    }

    // Commit cada 500 filas e informar progreso
    if ($insertados % 500 === 0) {
        $pdo->commit();
        $pdo->beginTransaction();
        echo "  $insertados filas insertadas...\n";
    }
}

$pdo->commit();
fclose($handle);

echo "\n=== Resumen ===\n";
echo "Insertados : $insertados\n";
echo "Errores    : $errores\n";
echo "Fin        : " . date('H:i:s') . "\n";
