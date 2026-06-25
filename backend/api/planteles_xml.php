<?php
// ============================================================
//  ECOEMS — api/planteles_xml.php
//  Extracción de datos de la base de datos en formato XML.
//  Reutiliza los mismos 3 criterios de búsqueda del catálogo:
//
//  GET /api/planteles_xml.php
//      ?q=texto         (clave o nombre, búsqueda por substring)
//      ?subsistema=X    (institución, coincidencia exacta)
//      ?municipio=X     (municipio/alcaldía, coincidencia exacta)
//      ?descargar=1     (fuerza descarga del archivo en vez de mostrarlo)
// ============================================================
require_once __DIR__ . '/../config.php';

header('Access-Control-Allow-Origin: *');

$pdo        = getDB();
$q          = trim($_GET['q'] ?? '');
$subsistema = trim($_GET['subsistema'] ?? '');
$municipio  = trim($_GET['municipio'] ?? '');

$sql    = "SELECT clave, nombre, especialidad, subsistema, municipio, estado, direccion, latitud, longitud FROM planteles WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (clave LIKE :q OR nombre LIKE :q2)";
    $params[':q']  = $q . '%';
    $params[':q2'] = '%' . $q . '%';
}
if ($subsistema !== '') {
    $sql .= " AND subsistema = :subsistema";
    $params[':subsistema'] = $subsistema;
}
if ($municipio !== '') {
    $sql .= " AND municipio = :municipio";
    $params[':municipio'] = $municipio;
}
$sql .= " ORDER BY subsistema, nombre";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$planteles = $stmt->fetchAll();

$doc = new DOMDocument('1.0', 'UTF-8');
$doc->formatOutput = true;

$raiz = $doc->createElement('planteles');
$raiz->setAttribute('total', (string)count($planteles));
$raiz->setAttribute('generado', date('c'));
$doc->appendChild($raiz);

foreach ($planteles as $p) {
    $nodo = $doc->createElement('plantel');
    foreach ($p as $campo => $valor) {
        $hijo = $doc->createElement($campo);
        $hijo->appendChild($doc->createTextNode((string)($valor ?? '')));
        $nodo->appendChild($hijo);
    }
    $raiz->appendChild($nodo);
}

header('Content-Type: application/xml; charset=utf-8');
if (!empty($_GET['descargar'])) {
    header('Content-Disposition: attachment; filename="planteles.xml"');
}
echo $doc->saveXML();
