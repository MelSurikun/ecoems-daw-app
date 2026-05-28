<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';

$pdo = getDB();
$q       = trim($_GET['q']       ?? '');
$plantel = trim($_GET['plantel'] ?? '');

if ($plantel) {
    // Look up plantel name from catalog
    $stmtP = $pdo->prepare("SELECT nombre FROM planteles WHERE clave = ?");
    $stmtP->execute([$plantel]);
    $plantelRow = $stmtP->fetch();
    $nombre = $plantelRow ? $plantelRow['nombre'] : null;

    // Stats from sustentantes
    $stmt = $pdo->prepare("
        SELECT
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

    $tieneDatos = $datos && $datos['total_solicitudes'] > 0;

    echo json_encode([
        'status' => 'ok',
        'datos' => [
            'clave_plantel'  => $plantel,
            'nombre'         => $nombre,
            'tiene_datos'    => $tieneDatos,
            'total_solicitudes' => $tieneDatos ? $datos['total_solicitudes'] : null,
            'asignados'         => $tieneDatos ? $datos['asignados'] : null,
            'puntaje_corte_min' => $tieneDatos ? $datos['puntaje_corte_min'] : null,
            'puntaje_corte_max' => $tieneDatos ? $datos['puntaje_corte_max'] : null,
            'puntaje_corte_prom'=> $tieneDatos ? $datos['puntaje_corte_prom'] : null,
            'promedio_cert'     => $tieneDatos ? $datos['promedio_cert'] : null,
            'hombres'           => $tieneDatos ? $datos['hombres'] : null,
            'mujeres'           => $tieneDatos ? $datos['mujeres'] : null,
        ]
    ]);

} elseif ($q) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.opc_ed01 AS clave_plantel,
               COALESCE(p.nombre, '') AS nombre,
               COUNT(*) AS solicitudes
        FROM sustentantes s
        LEFT JOIN planteles p ON p.clave = s.opc_ed01
        WHERE s.opc_ed01 LIKE :q AND s.opc_ed01 IS NOT NULL
        GROUP BY s.opc_ed01
        ORDER BY solicitudes DESC
        LIMIT 20
    ");
    $stmt->execute([':q' => $q . '%']);
    echo json_encode(['status' => 'ok', 'datos' => $stmt->fetchAll()]);

} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'Parámetro q o plantel requerido']);
}
