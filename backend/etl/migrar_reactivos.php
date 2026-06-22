<?php
/**
 * ETL — Migrar los 128 reactivos hardcodeados en frontend/simulador.php
 * a las tablas examenes, reactivos y figuras de la BD.
 *
 * Uso:
 *   php backend/etl/migrar_reactivos.php
 *
 * Requisito: la BD debe tener las tablas creadas (schema.sql).
 */
require_once __DIR__ . '/../config.php';

$simuladorPath = __DIR__ . '/../../frontend/simulador.php';
$contenido = file_get_contents($simuladorPath);

// 1) Extraer array DATA (reactivos)
preg_match('/const DATA\s*=\s*(\[[\s\S]*?\]);/s', $contenido, $m);
if (!$m) {
    fwrite(STDERR, "ERROR: No se encontró const DATA en simulador.php\n");
    exit(1);
}
$DATA = json_decode($m[1], true);
if (!$DATA || !is_array($DATA)) {
    fwrite(STDERR, "ERROR: No se pudo parsear DATA JSON\n");
    exit(1);
}
echo "→ Encontrados " . count($DATA) . " reactivos en DATA\n";

// 2) Extraer objeto FIGS (figuras)
preg_match('/const FIGS\s*=\s*(\{[\s\S]*?\});/s', $contenido, $m);
$FIGS = [];
if ($m) {
    $FIGS = json_decode($m[1], true) ?? [];
    echo "→ Encontradas " . count($FIGS) . " figuras\n";
}

$pdo = getDB();

// 3) Insertar o verificar el examen por defecto
$stmt = $pdo->query("SELECT id FROM examenes WHERE id = 1");
$examen = $stmt->fetch();
if (!$examen) {
    $pdo->exec("INSERT INTO examenes (id, nombre, descripcion, activo) VALUES (1, 'Simulacro COMIPEMS', 'Examen simulado de 128 reactivos tipo COMIPEMS', 1)");
    echo "→ Examen por defecto creado\n";
}

// 4) Insertar figuras
$stmtFig = $pdo->prepare("INSERT IGNORE INTO figuras (clave, datos) VALUES (?, ?)");
foreach ($FIGS as $clave => $base64) {
    $stmtFig->execute([$clave, $base64]);
}
echo "→ Figuras insertadas: " . count($FIGS) . "\n";

// 5) Insertar reactivos
$pdo->beginTransaction();
$stmtReact = $pdo->prepare("INSERT INTO reactivos (examen_id, numero, materia, pregunta, opciones, respuesta, contexto, figura_clave, passage, activo)
                              VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, 1)
                              ON DUPLICATE KEY UPDATE materia=VALUES(materia), pregunta=VALUES(pregunta), opciones=VALUES(opciones),
                                                      respuesta=VALUES(respuesta), contexto=VALUES(contexto), figura_clave=VALUES(figura_clave),
                                                      passage=VALUES(passage), activo=1");

$insertados = 0;
foreach ($DATA as $r) {
    $stmtReact->execute([
        $r['n'],
        $r['s'] ?? '',
        $r['q'] ?? '',
        json_encode($r['o'] ?? [], JSON_UNESCAPED_UNICODE),
        $r['a'] ?? 'A',
        $r['ctx'] ?? null,
        $r['fig'] ?? null,
        $r['passage'] ?? null,
    ]);
    $insertados++;
}

$pdo->commit();
echo "→ Reactivos insertados/actualizados: $insertados\n";
echo "✅ Migración completada.\n";
