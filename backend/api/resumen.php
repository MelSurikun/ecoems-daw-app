<?php
// ============================================================
//  ECOEMS — api/resumen.php
//  Estadísticas generales del proceso COMIPEMS 2024
//
//  GET /api/resumen.php
//  GET /api/resumen.php?institucion=U6   → filtrar por institución
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';

$pdo   = getDB();
$inst  = trim($_GET['institucion'] ?? '');
$where = $inst ? "AND cveins_asi = :inst" : "";
$params = $inst ? [':inst' => $inst] : [];

// Totales generales
$stmt = $pdo->prepare("
    SELECT
        COUNT(*)                        AS total_sustentantes,
        SUM(pre_exa='S')                AS presentaron_examen,
        SUM(expl_fin='ASI')             AS asignados,
        ROUND(AVG(nglobal),1)           AS promedio_global,
        ROUND(AVG(pnmat),1)             AS pct_matematicas,
        ROUND(AVG(pnesp),1)             AS pct_espanol,
        ROUND(AVG(pnhis),1)             AS pct_historia,
        ROUND(AVG(pnbio),1)             AS pct_biologia,
        ROUND(AVG(pnfis),1)             AS pct_fisica,
        ROUND(AVG(pnqui),1)             AS pct_quimica,
        SUM(sexo='H')                   AS hombres,
        SUM(sexo='M')                   AS mujeres
    FROM sustentantes
    WHERE pre_exa='S' $where
");
$stmt->execute($params);
$totales = $stmt->fetch();

// Top 10 planteles más solicitados (con nombre desde catálogo)
$stmt2 = $pdo->prepare("
    SELECT s.opc_ed01 AS clave, COALESCE(p.nombre, '') AS nombre, COUNT(*) AS solicitudes
    FROM sustentantes s
    LEFT JOIN planteles p ON p.clave = s.opc_ed01
    WHERE s.opc_ed01 IS NOT NULL AND pre_exa='S'
    GROUP BY s.opc_ed01
    ORDER BY solicitudes DESC
    LIMIT 10
");
$stmt2->execute();
$top_planteles = $stmt2->fetchAll();

// Distribución por institución asignada (usando vista)
$stmt3 = $pdo->query("
    SELECT * FROM v_resumen_instituciones ORDER BY asignados DESC
");
$por_institucion = $stmt3->fetchAll();

$nombresInst = [
    'U6' => 'UNAM', 'I5' => 'IPN', 'B0' => 'COLBACH',
    'C1' => 'CONALEP', 'D4' => 'DGETI', 'G2' => 'IEMS',
];
foreach ($por_institucion as &$fila) {
    $fila['institucion'] = $nombresInst[$fila['clave_inst']] ?? $fila['clave_inst'];
}
unset($fila);

echo json_encode([
    'status'          => 'ok',
    'totales'         => $totales,
    'top_planteles'   => $top_planteles,
    'por_institucion' => $por_institucion,
]);
