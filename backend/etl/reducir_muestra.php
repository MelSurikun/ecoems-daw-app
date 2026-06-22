<?php
// ============================================================
//  ECOEMS — etl/reducir_muestra.php
//  Reduce la tabla `sustentantes` a una muestra estratificada
//  proporcional, conservando las proporciones por institución
//  asignada (cveins_asi) y resultado final (expl_fin), para que
//  cortes y demanda por plantel sigan siendo representativos.
//
//  Uso (en tu VM Debian):
//    php reducir_muestra.php --fraccion=0.10
//
//  La tabla original se respalda como `sustentantes_full` antes
//  de truncar `sustentantes`. Si `sustentantes_full` ya existe,
//  el script reconstruye la muestra a partir de ella (permite
//  re-ejecutar con otra fracción sin perder los datos originales).
// ============================================================

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("Solo uso CLI\n");
}

require_once __DIR__ . '/../config.php';

$opts     = getopt('', ['fraccion::']);
$fraccion = isset($opts['fraccion']) ? (float)$opts['fraccion'] : 0.10;

if ($fraccion <= 0 || $fraccion >= 1) {
    echo "ERROR: --fraccion debe ser un decimal entre 0 y 1 (ej. 0.10 para 10%).\n";
    exit(1);
}

echo "=== Muestreo estratificado ECOEMS — Inicio: " . date('H:i:s') . " ===\n";
echo "Fracción objetivo: " . ($fraccion * 100) . "%\n\n";

$pdo = getDB();

// 1) Respaldar la tabla completa una sola vez.
$existe = $pdo->query("SHOW TABLES LIKE 'sustentantes_full'")->fetch();
if (!$existe) {
    echo "Respaldando sustentantes -> sustentantes_full ...\n";
    $pdo->exec("RENAME TABLE sustentantes TO sustentantes_full");
    $crear = $pdo->query("SHOW CREATE TABLE sustentantes_full")->fetch();
    $ddl = preg_replace(
        '/CREATE TABLE `sustentantes_full`/',
        'CREATE TABLE `sustentantes`',
        $crear['Create Table']
    );
    $pdo->exec($ddl);
    echo "Tabla `sustentantes` recreada (vacía) con el mismo esquema.\n\n";
} else {
    echo "`sustentantes_full` ya existe; se usará como fuente. Vaciando `sustentantes` actual...\n";
    $pdo->exec("TRUNCATE TABLE sustentantes");
}

// 2) Obtener los estratos (institución asignada + resultado final).
$estratos = $pdo->query("
    SELECT cveins_asi, expl_fin, COUNT(*) AS total
    FROM sustentantes_full
    GROUP BY cveins_asi, expl_fin
")->fetchAll();

echo "Estratos encontrados: " . count($estratos) . "\n";

$insertados  = 0;
$totalOrigen = 0;

foreach ($estratos as $estrato) {
    $cveins  = $estrato['cveins_asi'];
    $expl    = $estrato['expl_fin'];
    $total   = (int)$estrato['total'];
    $totalOrigen += $total;
    $tomar   = (int)ceil($total * $fraccion);
    if ($tomar < 1) {
        $tomar = 1; // conservar al menos 1 registro por estrato no vacío
    }

    // IS NULL no se compara con = en SQL; se construye la condición aparte.
    $condCveins = $cveins === null ? 'cveins_asi IS NULL' : 'cveins_asi = :cveins';
    $condExpl   = $expl   === null ? 'expl_fin IS NULL'   : 'expl_fin = :expl';

    $sql = "INSERT INTO sustentantes
            SELECT * FROM sustentantes_full
            WHERE $condCveins AND $condExpl
            ORDER BY RAND()
            LIMIT $tomar";

    $stmt = $pdo->prepare($sql);
    if ($cveins !== null) $stmt->bindValue(':cveins', $cveins);
    if ($expl   !== null) $stmt->bindValue(':expl', $expl);
    $stmt->execute();

    $insertados += $tomar;
}

echo "\nTotal original (sustentantes_full): " . number_format($totalOrigen) . "\n";
echo "Total en muestra (sustentantes):     " . number_format($insertados) . "\n";
echo "Proporción real obtenida:            " . round($insertados / max($totalOrigen, 1) * 100, 2) . "%\n";

// 3) Reporte de verificación: comparar proporciones por institución.
echo "\n=== Verificación de representatividad (top 10 instituciones) ===\n";
$verif = $pdo->query("
    SELECT
        f.cveins_asi,
        ROUND(100 * f.total_full / (SELECT COUNT(*) FROM sustentantes_full), 2) AS pct_full,
        ROUND(100 * m.total_muestra / (SELECT COUNT(*) FROM sustentantes), 2)   AS pct_muestra
    FROM
        (SELECT cveins_asi, COUNT(*) AS total_full FROM sustentantes_full GROUP BY cveins_asi) f
    JOIN
        (SELECT cveins_asi, COUNT(*) AS total_muestra FROM sustentantes GROUP BY cveins_asi) m
        ON f.cveins_asi = m.cveins_asi OR (f.cveins_asi IS NULL AND m.cveins_asi IS NULL)
    ORDER BY f.total_full DESC
    LIMIT 10
")->fetchAll();

printf("%-10s %10s %12s\n", 'Inst.', '% original', '% muestra');
foreach ($verif as $v) {
    printf("%-10s %9s%% %11s%%\n", $v['cveins_asi'] ?? 'NULL', $v['pct_full'], $v['pct_muestra']);
}

echo "\n=== Fin: " . date('H:i:s') . " ===\n";
echo "Nota: `sustentantes_full` se conserva como respaldo; las vistas\n";
echo "v_corte_por_plantel y v_resumen_instituciones ya reflejan la muestra.\n";
