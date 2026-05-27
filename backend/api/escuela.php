<?php
// ============================================================
//  ECOEMS — api/escuela.php
//  Búsqueda de plantel por clave de opción educativa
//
//  GET /api/escuela.php?plantel=B00001   → datos + estadísticas
//  GET /api/escuela.php?q=CETIS          → lista de planteles
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';

$pdo = getDB();
$q       = trim($_GET['q']       ?? '');
$plantel = trim($_GET['plantel'] ?? '');

if ($plantel) {
    // Estadísticas de UN plantel específico
    $stmt = $pdo->prepare("
        SELECT
            :plantel                                    AS clave_plantel,
            COUNT(*)                                    AS total_solicitudes,
            SUM(expl_fin = 'ASI')                       AS asignados,
            MIN(CASE WHEN expl_fin='ASI' THEN nglobal END) AS puntaje_corte_min,
            MAX(CASE WHEN expl_fin='ASI' THEN nglobal END) AS puntaje_corte_max,
            ROUND(AVG(CASE WHEN expl_fin='ASI' THEN nglobal END),1) AS puntaje_corte_prom,
            ROUND(AVG(promedio),1)                      AS promedio_cert,
            SUM(sexo='H')                               AS hombres,
            SUM(sexo='M')                               AS mujeres
        FROM sustentantes
        WHERE pre_exa = 'S'
          AND (opc_ed01 = :plantel OR opc_ed02 = :plantel OR opc_ed03 = :plantel)
    ");
    $stmt->execute([':plantel' => $plantel]);
    $datos = $stmt->fetch();
    echo json_encode(['status' => 'ok', 'datos' => $datos]);

} elseif ($q) {
    // Búsqueda por clave parcial (autocomplete)
    $stmt = $pdo->prepare("
        SELECT DISTINCT opc_ed01 AS clave_plantel, COUNT(*) AS solicitudes
        FROM sustentantes
        WHERE opc_ed01 LIKE :q AND opc_ed01 IS NOT NULL
        GROUP BY opc_ed01
        ORDER BY solicitudes DESC
        LIMIT 20
    ");
    $stmt->execute([':q' => $q . '%']);
    echo json_encode(['status' => 'ok', 'datos' => $stmt->fetchAll()]);

} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'Parámetro q o plantel requerido']);
}
