<?php
// ============================================================
//  ECOEMS — api/comparar.php
//  Compara estadísticas de varios planteles
//
//  GET /api/comparar.php?claves[]=B00001&claves[]=U60001
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';

$claves = $_GET['claves'] ?? [];
if (empty($claves) || count($claves) > 5) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'Envía entre 1 y 5 claves']);
    exit;
}

$pdo = getDB();
$resultados = [];

foreach ($claves as $clave) {
    $clave = trim($clave);
    $stmt = $pdo->prepare("
        SELECT
            ? AS clave_plantel,
            COUNT(*) AS total_solicitudes,
            SUM(expl_fin='ASI') AS asignados,
            MIN(CASE WHEN expl_fin='ASI' THEN nglobal END) AS puntaje_min,
            MAX(CASE WHEN expl_fin='ASI' THEN nglobal END) AS puntaje_max,
            ROUND(AVG(CASE WHEN expl_fin='ASI' THEN nglobal END),1) AS puntaje_prom,
            ROUND(AVG(CASE WHEN expl_fin='ASI' THEN pnmat END),1) AS pct_mat,
            ROUND(AVG(CASE WHEN expl_fin='ASI' THEN pnesp END),1) AS pct_esp,
            ROUND(AVG(CASE WHEN expl_fin='ASI' THEN pnhis END),1) AS pct_his
        FROM sustentantes
        WHERE pre_exa='S'
          AND (opc_ed01=? OR opc_ed02=? OR opc_ed03=?)
    ");
    $stmt->execute([$clave, $clave, $clave, $clave]);
    $resultados[] = $stmt->fetch();
}

echo json_encode(['status' => 'ok', 'datos' => $resultados]);
