<?php
// ============================================================
//  ECOEMS — api/metas.php
//  Perfil del aspirante: opciones de interés y puntaje meta
//
//  GET /api/metas.php   → perfil del usuario en sesión
//  PUT /api/metas.php   → guarda { puntaje_meta, opciones:[claves] }
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../auth.php';

$pdo    = getDB();
$metodo = $_SERVER['REQUEST_METHOD'];
$usuario = requiereSesion();

if ($metodo === 'GET') {
    $stmt = $pdo->prepare("SELECT puntaje_meta, opciones_json FROM perfil_aspirante WHERE usuario_id = ?");
    $stmt->execute([$usuario['id']]);
    $row = $stmt->fetch();

    $claves = $row && $row['opciones_json'] ? json_decode($row['opciones_json'], true) : [];

    $opciones = [];
    $metaSugerida = null;
    if ($claves) {
        $stmtP = $pdo->prepare("
            SELECT p.clave, p.nombre, p.subsistema,
                   v.puntaje_corte_prom
            FROM planteles p
            LEFT JOIN v_corte_por_plantel v ON v.clave_plantel = p.clave
            WHERE p.clave = ?
        ");
        foreach ($claves as $clave) {
            $stmtP->execute([$clave]);
            $opcion = $stmtP->fetch();
            if ($opcion) {
                if ($opcion['puntaje_corte_prom'] !== null) {
                    $opcion['puntaje_corte_prom'] = (int)round($opcion['puntaje_corte_prom']);
                    $metaSugerida = max($metaSugerida ?? 0, $opcion['puntaje_corte_prom']);
                }
                $opciones[] = $opcion;
            }
        }
    }

    echo json_encode([
        'status' => 'ok',
        'datos' => [
            'puntaje_meta'   => $row ? $row['puntaje_meta'] : null,
            'meta_sugerida'  => $metaSugerida,
            'opciones'       => $opciones,
        ]
    ]);
    exit;
}

if ($metodo === 'PUT') {
    $input    = json_decode(file_get_contents('php://input'), true) ?? [];
    $opciones = $input['opciones'] ?? [];
    $meta     = $input['puntaje_meta'] ?? null;

    if (!is_array($opciones) || count($opciones) > 5) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'Máximo 5 opciones.']);
        exit;
    }

    $opciones = array_values(array_unique(array_map('strval', $opciones)));

    if ($opciones) {
        $placeholders = implode(',', array_fill(0, count($opciones), '?'));
        $stmtV = $pdo->prepare("SELECT clave FROM planteles WHERE clave IN ($placeholders)");
        $stmtV->execute($opciones);
        $validas = $stmtV->fetchAll(PDO::FETCH_COLUMN);
        if (count($validas) !== count($opciones)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'mensaje' => 'Una o más claves de plantel no existen.']);
            exit;
        }
    }

    $meta = $meta !== null ? max(0, min(128, (int)$meta)) : null;
    $opcionesJson = json_encode($opciones, JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare("
        INSERT INTO perfil_aspirante (usuario_id, puntaje_meta, opciones_json)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE puntaje_meta = VALUES(puntaje_meta), opciones_json = VALUES(opciones_json)
    ");
    $stmt->execute([$usuario['id'], $meta, $opcionesJson]);

    echo json_encode(['status' => 'ok']);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'mensaje' => 'Método no soportado.']);
