<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

const MAX_ROWS_PREVIEW = 20;
const MAX_ZONES_PREVIEW = 12;
const MAX_ANALYSIS_ROWS = 4000;
const MAX_CHART_ITEMS = 8;
const CACHE_DIR = __DIR__ . '/cache';

if (!is_dir(CACHE_DIR)) {
    @mkdir(CACHE_DIR, 0775, true);
}

function h($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function showOrColumn($value, $columnName)
{
    $text = trim((string) $value);
    if ($text === '' || strtolower($text) === 'undefined' || strtolower($text) === 'null') {
        return '[' . $columnName . ']';
    }

    return $text;
}

function toFloat($value)
{
    if ($value === null) {
        return null;
    }

    $normalized = str_replace(',', '.', trim((string) $value));
    if ($normalized === '' || !is_numeric($normalized)) {
        return null;
    }

    return (float) $normalized;
}

function normalizeHeader($text)
{
    $text = strtoupper(trim((string) $text));
    $text = preg_replace('/\s+/', '_', $text);
    return $text !== '' ? $text : 'COL';
}

function containsText($haystack, $needle)
{
    if ($needle === '') {
        return true;
    }

    return strpos((string) $haystack, (string) $needle) !== false;
}

function splitCellReference($ref)
{
    if (!preg_match('/^([A-Z]+)(\d+)$/', (string) $ref, $m)) {
        return ['', 0];
    }

    return [$m[1], (int) $m[2]];
}

function rowFirst($row, $aliases, $default = '')
{
    foreach ($aliases as $key) {
        if (isset($row[$key]) && trim((string) $row[$key]) !== '') {
            return $row[$key];
        }
    }

    return $default;
}

function resolveServerDataFile()
{
    $candidates = [
        __DIR__ . '/BD_SUSTENTANTES_2024.xlsx',
        __DIR__ . '/../BD_SUSTENTANTES_2024.xlsx',
        __DIR__ . '/BD_SUSTENTANTES_2024.csv',
        __DIR__ . '/../BD_SUSTENTANTES_2024.csv',
    ];

    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            return $candidate;
        }
    }

    return null;
}

function readXlsxSharedStrings($filePath)
{
    $strings = [];

    $reader = new XMLReader();
    $uri = 'zip://' . $filePath . '#xl/sharedStrings.xml';
    if (!$reader->open($uri, null, LIBXML_NONET | LIBXML_COMPACT)) {
        return $strings;
    }

    while ($reader->read()) {
        if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'si') {
            $siNode = new SimpleXMLElement($reader->readOuterXML());
            $value = '';
            if (isset($siNode->t)) {
                $value = (string) $siNode->t;
            } else {
                foreach ($siNode->r as $run) {
                    $value .= (string) $run->t;
                }
            }
            $strings[] = $value;
        }
    }

    $reader->close();
    return $strings;
}

function getFirstWorksheetPathFromZip($zip)
{
    $candidates = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = (string) $zip->getNameIndex($i);
        if (preg_match('#^xl/worksheets/.+\.xml$#', $name) === 1) {
            $candidates[] = $name;
        }
    }

    if ($candidates === []) {
        return null;
    }

    sort($candidates, SORT_NATURAL | SORT_FLAG_CASE);
    return $candidates[0];
}

function iterateXlsxRows($filePath)
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('La extension ZipArchive de PHP no esta habilitada.');
    }
    if (!class_exists('XMLReader')) {
        throw new RuntimeException('La extension XMLReader de PHP no esta habilitada.');
    }

    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        throw new RuntimeException('No se pudo abrir el archivo XLSX.');
    }

    $sharedStrings = readXlsxSharedStrings($filePath);
    $sheetPath = getFirstWorksheetPathFromZip($zip);
    if ($sheetPath === null) {
        $zip->close();
        throw new RuntimeException('No se encontro ninguna hoja en el XLSX.');
    }

    $reader = new XMLReader();
    $sheetUri = 'zip://' . $filePath . '#' . $sheetPath;
    if (!$reader->open($sheetUri, null, LIBXML_NONET | LIBXML_COMPACT)) {
        $zip->close();
        throw new RuntimeException('No se pudo parsear XML de la hoja XLSX.');
    }

    $headers = [];
    while ($reader->read()) {
        if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'row') {
            continue;
        }

        $rowXml = new SimpleXMLElement($reader->readOuterXML());
        $rowValues = [];

        foreach ($rowXml->c as $cell) {
            $ref = (string) ($cell['r'] ?? '');
            list($colLetters) = splitCellReference($ref);
            $type = (string) ($cell['t'] ?? '');
            $raw = '';

            if (isset($cell->v)) {
                $raw = (string) $cell->v;
            }

            if ($type === 's') {
                $idx = (int) $raw;
                $value = isset($sharedStrings[$idx]) ? $sharedStrings[$idx] : '';
            } elseif ($type === 'inlineStr') {
                $value = isset($cell->is->t) ? (string) $cell->is->t : '';
            } else {
                $value = $raw;
            }

            $rowValues[$colLetters] = trim((string) $value);
        }

        if ($headers === []) {
            foreach ($rowValues as $key => $headerText) {
                $headers[$key] = normalizeHeader($headerText);
            }
            continue;
        }

        $assoc = [];
        foreach ($headers as $col => $header) {
            $assoc[$header] = isset($rowValues[$col]) ? $rowValues[$col] : null;
        }

        $isEmpty = true;
        foreach ($assoc as $value) {
            if (trim((string) $value) !== '') {
                $isEmpty = false;
                break;
            }
        }

        if (!$isEmpty) {
            yield $assoc;
        }
    }

    $reader->close();
    $zip->close();
}

function iterateCsvRows($filePath)
{
    $handle = fopen($filePath, 'rb');
    if ($handle === false) {
        throw new RuntimeException('No se pudo abrir el archivo CSV.');
    }

    $headers = [];
    while (($row = fgetcsv($handle)) !== false) {
        if ($headers === []) {
            foreach ($row as $i => $headerText) {
                $headers[$i] = normalizeHeader($headerText);
            }
            continue;
        }

        $assoc = [];
        foreach ($headers as $i => $header) {
            $assoc[$header] = isset($row[$i]) ? $row[$i] : null;
        }

        $isEmpty = true;
        foreach ($assoc as $value) {
            if (trim((string) $value) !== '') {
                $isEmpty = false;
                break;
            }
        }

        if (!$isEmpty) {
            yield $assoc;
        }
    }

    fclose($handle);
}

function iterateRows($filePath)
{
    if (!is_file($filePath)) {
        throw new RuntimeException('No se encontro el archivo de datos en: ' . $filePath);
    }

    $ext = strtolower((string) pathinfo((string) $filePath, PATHINFO_EXTENSION));
    if ($ext === 'csv') {
        yield from iterateCsvRows($filePath);
        return;
    }

    if ($ext === 'xlsx') {
        if (!class_exists('ZipArchive')) {
            $csvAlternative = preg_replace('/\.xlsx$/i', '.csv', (string) $filePath);
            if ($csvAlternative !== null && is_file($csvAlternative)) {
                yield from iterateCsvRows($csvAlternative);
                return;
            }

            throw new RuntimeException(
                'La extension ZipArchive de PHP no esta habilitada para leer XLSX. ' .
                'Opciones: 1) instalar php-zip en servidor, 2) subir BD_SUSTENTANTES_2024.csv en la misma carpeta, ' .
                'o 3) usar el analisis local en navegador (seccion inferior).'
            );
        }

        yield from iterateXlsxRows($filePath);
        return;
    }

    throw new RuntimeException('Formato no soportado. Usa .xlsx o .csv');
}

function hasInternet($row)
{
    $internetRaw = strtoupper(trim((string) rowFirst($row, ['INTERNET', 'SER_INTE', 'SER_CABL', 'SER_TABL'], '')));
    $compuRaw = strtoupper(trim((string) rowFirst($row, ['COMPU', 'BIEN_PC', 'SER_PC', 'SER_TABL'], '')));

    if ($internetRaw !== '') {
        if (preg_match('/^1$/', $internetRaw) === 1) {
            return true;
        }
        if (preg_match('/^2$|^0$/', $internetRaw) === 1) {
            return false;
        }
        if (preg_match('/NO|SIN|0/', $internetRaw) === 1) {
            return false;
        }
        if (preg_match('/SI|S[IÍ]|1/', $internetRaw) === 1) {
            return true;
        }
    }

    if (preg_match('/SIN INTERNET|NO INTERNET/', $compuRaw) === 1) {
        return false;
    }

    return preg_match('/INTERNET|WIFI/', $compuRaw) === 1;
}

function classify($row)
{
    $promedio = toFloat(rowFirst($row, ['PROMEDIO'], null));
    $ingreso = toFloat(rowFirst($row, ['ING_MEN'], null));
    $municipio = strtoupper(trim((string) rowFirst($row, ['ALCMUN_ASP', 'MUNALC_ESC', 'COLONIA'], 'SIN DATO')));
    $segRaw = strtoupper(trim((string) rowFirst($row, ['REC_ALI', 'REC_HAM', 'REC_DCC'], '')));
    $compuRaw = strtoupper(trim((string) rowFirst($row, ['COMPU', 'BIEN_PC', 'SER_TABL'], '')));
    $internet = hasInternet($row);

    if ($ingreso === null) {
        $nivelSocio = 'Sin dato';
    } elseif ($ingreso < 4000) {
        $nivelSocio = 'Muy bajo';
    } elseif ($ingreso < 8000) {
        $nivelSocio = 'Bajo';
    } elseif ($ingreso < 15000) {
        $nivelSocio = 'Medio';
    } else {
        $nivelSocio = 'Alto';
    }

    if ($promedio === null) {
        $desempeno = 'Sin dato';
    } elseif ($promedio >= 9.0) {
        $desempeno = 'Excelente';
    } elseif ($promedio >= 8.0) {
        $desempeno = 'Bueno';
    } elseif ($promedio >= 7.0) {
        $desempeno = 'Regular';
    } else {
        $desempeno = 'En riesgo';
    }

    if ($municipio === '' || $municipio === 'SIN DATO') {
        $ubicacion = 'Sin dato';
    } elseif (
        containsText($municipio, 'METROPOLIT') ||
        containsText($municipio, 'CDMX') ||
        containsText($municipio, 'MONTERREY') ||
        containsText($municipio, 'GUADALAJARA')
    ) {
        $ubicacion = 'Metropolitana';
    } elseif (containsText($municipio, 'URB')) {
        $ubicacion = 'Urbana';
    } else {
        $ubicacion = 'Rural o periurbana';
    }

    if ($segRaw === '' || $segRaw === 'NA' || $segRaw === 'N/A') {
        $seguridad = 'Sin dato';
    } elseif (preg_match('/SEVERA|ALTA|NO COME|HAMBRE/', $segRaw) === 1) {
        $seguridad = 'Inseguridad severa';
    } elseif (preg_match('/MODERADA|MEDIA|A VECES/', $segRaw) === 1) {
        $seguridad = 'Inseguridad moderada';
    } elseif (preg_match('/SI|S[IÍ]|ADECUADA|SUFICIENTE|ESTABLE/', $segRaw) === 1) {
        $seguridad = 'Seguridad adecuada';
    } else {
        $seguridad = 'Revisar catalogo';
    }

    $hasComputer = preg_match('/SI|S[IÍ]|1|COMPUTADORA|LAPTOP|PC/', $compuRaw) === 1;
    if ($hasComputer && $internet) {
        $equipamiento = 'Completo (computadora + internet)';
    } elseif ($hasComputer || $internet) {
        $equipamiento = 'Parcial (solo uno)';
    } else {
        $equipamiento = 'Limitado (sin equipo e internet)';
    }

    return [
        'nivel_socioeconomico' => $nivelSocio,
        'desempeno_academico' => $desempeno,
        'ubicacion_geografica' => $ubicacion,
        'seguridad_alimentaria' => $seguridad,
        'equipamiento_tecnologico' => $equipamiento,
        'acceso_internet' => $internet,
    ];
}

function rowMatchesFilters($row, $filters)
{
    $municipio = strtolower(trim((string) (isset($filters['municipio']) ? $filters['municipio'] : '')));
    $promedioMin = toFloat(isset($filters['promedio_min']) ? $filters['promedio_min'] : null);
    $sinInternet = ((isset($filters['sin_internet']) ? $filters['sin_internet'] : '') === '1');

    $groupA = true;
    if ($municipio !== '') {
        $actualMunicipio = strtolower((string) rowFirst($row, ['ALCMUN_ASP', 'MUNALC_ESC', 'COLONIA'], ''));
        $groupA = $groupA && containsText($actualMunicipio, $municipio);
    }

    if ($promedioMin !== null) {
        $prom = toFloat(rowFirst($row, ['PROMEDIO'], null));
        $groupA = $groupA && $prom !== null && $prom > $promedioMin;
    }

    if ($sinInternet) {
        $groupA = $groupA && ((isset($row['acceso_internet']) ? $row['acceso_internet'] : true) === false);
    }

    $sexo = strtoupper(trim((string) (isset($filters['sexo']) ? $filters['sexo'] : '')));
    $nivel = trim((string) (isset($filters['nivel_socioeconomico']) ? $filters['nivel_socioeconomico'] : ''));
    $seg = trim((string) (isset($filters['seguridad_alimentaria']) ? $filters['seguridad_alimentaria'] : ''));

    $conds = [];
    if ($sexo !== '') {
        $conds[] = strtoupper((string) rowFirst($row, ['SEXO'], '')) === $sexo;
    }
    if ($nivel !== '') {
        $conds[] = (string) (isset($row['nivel_socioeconomico']) ? $row['nivel_socioeconomico'] : '') === $nivel;
    }
    if ($seg !== '') {
        $conds[] = (string) (isset($row['seguridad_alimentaria']) ? $row['seguridad_alimentaria'] : '') === $seg;
    }

    $groupB = true;
    if ($conds !== []) {
        $groupB = !in_array(false, $conds, true);
    }

    return $groupA && $groupB;
}

function summarizeFilters($filters, $matched, $total)
{
    $parts = [];
    if (!empty($filters['municipio'])) {
        $parts[] = 'municipio contiene "' . (string) $filters['municipio'] . '"';
    }
    if (!empty($filters['promedio_min'])) {
        $parts[] = 'promedio > ' . (string) $filters['promedio_min'];
    }
    if ((isset($filters['sin_internet']) ? $filters['sin_internet'] : '') === '1') {
        $parts[] = 'sin acceso a internet';
    }
    if (!empty($filters['sexo'])) {
        $parts[] = 'sexo = ' . (string) $filters['sexo'];
    }
    if (!empty($filters['nivel_socioeconomico'])) {
        $parts[] = 'nivel socioeconomico = ' . (string) $filters['nivel_socioeconomico'];
    }
    if (!empty($filters['seguridad_alimentaria'])) {
        $parts[] = 'seguridad alimentaria = ' . (string) $filters['seguridad_alimentaria'];
    }

    if ($parts === []) {
        return 'Se analizaron ' . $total . ' registros sin filtros. Resultados mostrados: ' . $matched . '.';
    }

    return 'Se aplicaron filtros anidados (' . implode(', ', $parts) . '). Coincidencias: ' . $matched . ' de ' . $total . '.';
}

function topMap($map, $limit)
{
    arsort($map);
    return array_slice($map, 0, $limit, true);
}

function limitMapForChart($map, $limit)
{
    if (!is_array($map) || $map === []) {
        return [];
    }

    arsort($map);
    if (count($map) <= $limit) {
        return $map;
    }

    $keep = array_slice($map, 0, $limit - 1, true);
    $rest = array_slice($map, $limit - 1, null, true);
    $others = 0;
    foreach ($rest as $value) {
        $others += (int) $value;
    }
    $keep['Otros'] = $others;

    return $keep;
}

function fileSizeLabel($bytes)
{
    $bytes = (int) $bytes;
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    if ($bytes < 1024 * 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }
    return round($bytes / 1048576, 2) . ' MB';
}


function buildCacheKey($filePath, $filters)
{
    $meta = [
        'path' => $filePath,
        'mtime' => is_file($filePath) ? (int) filemtime($filePath) : 0,
        'size' => is_file($filePath) ? (int) filesize($filePath) : 0,
        'filters' => $filters,
        'max_rows' => MAX_ANALYSIS_ROWS,
    ];

    return hash('sha256', json_encode($meta));
}

function readCache($cacheKey)
{
    $cachePath = CACHE_DIR . '/' . $cacheKey . '.json';
    if (!is_file($cachePath)) {
        return null;
    }

    $raw = file_get_contents($cachePath);
    if ($raw === false) {
        return null;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || !isset($decoded['result'])) {
        return null;
    }

    return $decoded['result'];
}

function writeCache($cacheKey, $result)
{
    $cachePath = CACHE_DIR . '/' . $cacheKey . '.json';
    $payload = [
        'saved_at' => date('c'),
        'result' => $result,
    ];

    @file_put_contents($cachePath, json_encode($payload));
}

$activeFile = resolveServerDataFile();

$filters = [
    'municipio' => isset($_GET['municipio']) ? (string) $_GET['municipio'] : '',
    'promedio_min' => isset($_GET['promedio_min']) ? (string) $_GET['promedio_min'] : '',
    'sin_internet' => isset($_GET['sin_internet']) ? (string) $_GET['sin_internet'] : '',
    'sexo' => isset($_GET['sexo']) ? (string) $_GET['sexo'] : '',
    'nivel_socioeconomico' => isset($_GET['nivel_socioeconomico']) ? (string) $_GET['nivel_socioeconomico'] : '',
    'seguridad_alimentaria' => isset($_GET['seguridad_alimentaria']) ? (string) $_GET['seguridad_alimentaria'] : '',
];

$error = null;
$result = [
    'total_registros' => 0,
    'registros_filtrados' => 0,
    'promedio_global_filtrado' => null,
    'conteo_genero' => [],
    'promedio_por_zona' => [],
    'conteo_aspectos' => [
        'nivel_socioeconomico' => [],
        'desempeno_academico' => [],
        'ubicacion_geografica' => [],
        'seguridad_alimentaria' => [],
        'equipamiento_tecnologico' => [],
    ],
    'internet' => ['Con internet' => 0, 'Sin internet' => 0],
    'top_municipios' => [],
    'preview' => [],
    'resumen' => '',
    'notice' => '',
];

try {
    if ($activeFile === null) {
        $result['notice'] = 'No se encontro BD_SUSTENTANTES_2024.xlsx en el servidor. Colocalo junto a index.php o en la carpeta padre.';
    } else {
        $cacheKey = buildCacheKey($activeFile, $filters);
        $cachedResult = readCache($cacheKey);
        if (is_array($cachedResult)) {
            $result = $cachedResult;
        } else {
            $sumProm = 0.0;
            $countProm = 0;
            $zoneAccum = [];
            $municipioCount = [];
            $processedRows = 0;

            foreach (iterateRows($activeFile) as $row) {
                $processedRows++;
                if ($processedRows > MAX_ANALYSIS_ROWS) {
                    $result['notice'] = 'Para mantener fluidez del navegador se analizaron los primeros ' . MAX_ANALYSIS_ROWS . ' registros del archivo activo.';
                    break;
                }

                $result['total_registros']++;

        $clas = classify($row);
        $enriched = array_merge($row, $clas);

        foreach ($result['conteo_aspectos'] as $aspect => $values) {
            $label = isset($clas[$aspect]) ? (string) $clas[$aspect] : 'Sin dato';
            $result['conteo_aspectos'][$aspect][$label] = (isset($result['conteo_aspectos'][$aspect][$label]) ? $result['conteo_aspectos'][$aspect][$label] : 0) + 1;
        }

                $m = trim((string) rowFirst($row, ['ALCMUN_ASP', 'MUNALC_ESC', 'COLONIA'], 'SIN DATO'));
        $municipioCount[$m] = (isset($municipioCount[$m]) ? $municipioCount[$m] : 0) + 1;

        if ($clas['acceso_internet']) {
            $result['internet']['Con internet']++;
        } else {
            $result['internet']['Sin internet']++;
        }

        if (!rowMatchesFilters($enriched, $filters)) {
            continue;
        }

        $result['registros_filtrados']++;

                $sexo = strtoupper(trim((string) rowFirst($row, ['SEXO'], 'SIN DATO')));
        $result['conteo_genero'][$sexo] = (isset($result['conteo_genero'][$sexo]) ? $result['conteo_genero'][$sexo] : 0) + 1;

                $prom = toFloat(rowFirst($row, ['PROMEDIO'], null));
        if ($prom !== null) {
            $sumProm += $prom;
            $countProm++;

                    $zona = trim((string) rowFirst($row, ['ALCMUN_ASP', 'MUNALC_ESC', 'COLONIA'], 'SIN DATO'));
            if (!isset($zoneAccum[$zona])) {
                $zoneAccum[$zona] = ['sum' => 0.0, 'count' => 0];
            }
            $zoneAccum[$zona]['sum'] += $prom;
            $zoneAccum[$zona]['count']++;
        }

        if (count($result['preview']) < MAX_ROWS_PREVIEW) {
            $result['preview'][] = [
                        'SEXO' => (string) rowFirst($row, ['SEXO'], ''),
                        'PROMEDIO' => (string) rowFirst($row, ['PROMEDIO'], ''),
                        'ALCMUN_ASP' => (string) rowFirst($row, ['ALCMUN_ASP', 'MUNALC_ESC', 'COLONIA'], ''),
                        'ING_MEN' => (string) rowFirst($row, ['ING_MEN'], ''),
                        'REC_ALI' => (string) rowFirst($row, ['REC_ALI', 'REC_HAM', 'REC_DCC'], ''),
                        'COMPU' => (string) rowFirst($row, ['COMPU', 'BIEN_PC', 'SER_TABL'], ''),
                'nivel_socioeconomico' => $clas['nivel_socioeconomico'],
                'desempeno_academico' => $clas['desempeno_academico'],
                'ubicacion_geografica' => $clas['ubicacion_geografica'],
                'seguridad_alimentaria' => $clas['seguridad_alimentaria'],
                'equipamiento_tecnologico' => $clas['equipamiento_tecnologico'],
                'acceso_internet' => $clas['acceso_internet'] ? 'SI' : 'NO',
            ];
        }
    }

            foreach ($zoneAccum as $zona => $acc) {
                $result['promedio_por_zona'][$zona] = $acc['count'] > 0 ? round($acc['sum'] / $acc['count'], 2) : null;
            }
            arsort($result['promedio_por_zona']);

            if ($countProm > 0) {
                $result['promedio_global_filtrado'] = round($sumProm / $countProm, 2);
            }

            $result['top_municipios'] = topMap($municipioCount, 10);
            $result['resumen'] = summarizeFilters($filters, (int) $result['registros_filtrados'], (int) $result['total_registros']);
            writeCache($cacheKey, $result);
        }
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}

$zonesTop = array_slice($result['promedio_por_zona'], 0, MAX_ZONES_PREVIEW, true);
$zonesTop = limitMapForChart($zonesTop, MAX_CHART_ITEMS);
$chartZonesLabels = array_keys($zonesTop);
$chartZonesValues = array_values($zonesTop);

$genderChartMap = limitMapForChart($result['conteo_genero'], MAX_CHART_ITEMS);
$chartGenderLabels = array_keys($genderChartMap);
$chartGenderValues = array_values($genderChartMap);

$academicChartMap = limitMapForChart($result['conteo_aspectos']['desempeno_academico'], MAX_CHART_ITEMS);
$chartAcademicLabels = array_keys($academicChartMap);
$chartAcademicValues = array_values($academicChartMap);

$socioChartMap = limitMapForChart($result['conteo_aspectos']['nivel_socioeconomico'], MAX_CHART_ITEMS);
$chartSocioLabels = array_keys($socioChartMap);
$chartSocioValues = array_values($socioChartMap);

$foodChartMap = limitMapForChart($result['conteo_aspectos']['seguridad_alimentaria'], MAX_CHART_ITEMS);
$chartFoodLabels = array_keys($foodChartMap);
$chartFoodValues = array_values($foodChartMap);

$internetChartMap = limitMapForChart($result['internet'], MAX_CHART_ITEMS);
$chartInternetLabels = array_keys($internetChartMap);
$chartInternetValues = array_values($internetChartMap);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Analitica Educativa | Sustentantes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Sora:wght@500;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --bg: #f3f6ff;
            --ink: #162033;
            --muted: #5a6783;
            --panel: rgba(255, 255, 255, 0.96);
            --line: rgba(36, 61, 110, 0.14);
            --brand: #0d9a8b;
            --brand-2: #ff8455;
            --brand-3: #355dba;
            --ok: #0f766e;
            --warn: #9a3412;
            --danger: #b91c1c;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            font-family: "Manrope", sans-serif;
            background:
                radial-gradient(circle at 8% 2%, #d9fff6 0, transparent 28%),
                radial-gradient(circle at 92% 9%, #ffe7d8 0, transparent 34%),
                radial-gradient(circle at 70% 80%, #dfe9ff 0, transparent 28%),
                var(--bg);
            position: relative;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
            filter: blur(40px);
            animation: drift 14s ease-in-out infinite;
        }

        body::before {
            width: 180px;
            height: 180px;
            left: -30px;
            bottom: 90px;
            background: rgba(26, 197, 165, 0.28);
        }

        body::after {
            width: 220px;
            height: 220px;
            right: -50px;
            top: 240px;
            background: rgba(244, 126, 90, 0.24);
            animation-delay: 2s;
        }

        .shell {
            max-width: 1300px;
            margin: 26px auto 40px;
            padding: 0 16px;
            position: relative;
            z-index: 1;
        }

        .hero {
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 24px;
            background:
                linear-gradient(140deg, rgba(13, 154, 139, 0.96), rgba(53, 93, 186, 0.93));
            color: #ffffff;
            box-shadow: 0 20px 44px rgba(24, 44, 88, 0.2);
            margin-bottom: 16px;
            position: relative;
            overflow: hidden;
        }

        .hero::after {
            content: "";
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            position: absolute;
            right: -70px;
            top: -70px;
        }

        .hero h1 {
            margin: 0;
            font-family: "Sora", sans-serif;
            font-size: clamp(1.45rem, 2.9vw, 2.1rem);
            line-height: 1.2;
            position: relative;
            z-index: 1;
        }

        .hero p {
            margin: 10px 0 0;
            max-width: 760px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            backdrop-filter: blur(10px);
            border-radius: 18px;
            padding: 16px;
            box-shadow: 0 12px 26px rgba(37, 48, 74, 0.09);
        }

        .mb16 {
            margin-bottom: 16px;
        }

        .status {
            border-radius: 12px;
            padding: 11px 12px;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .status.success {
            background: #dffaf3;
            border: 1px solid rgba(15, 118, 110, 0.22);
            color: var(--ok);
        }

        .status.error {
            background: #fde8e8;
            border: 1px solid rgba(185, 28, 28, 0.24);
            color: var(--danger);
        }

        .tool-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }

        .upload-box {
            display: grid;
            gap: 10px;
        }

        .compact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 10px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        input,
        select,
        button {
            width: 100%;
            border-radius: 10px;
            border: 1px solid #ced7e8;
            padding: 10px 11px;
            font-family: inherit;
            font-size: 14px;
            background: #ffffff;
        }

        .btn {
            border: none;
            color: #ffffff;
            background: linear-gradient(135deg, var(--brand), var(--brand-3));
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(28, 62, 141, 0.2);
        }

        .btn.secondary {
            background: linear-gradient(135deg, #f07f5d, #cf5d49);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .metric {
            padding: 14px;
            border-radius: 14px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.78));
            border: 1px solid var(--line);
        }

        .metric .k {
            margin-top: 6px;
            font-size: clamp(1.35rem, 2.8vw, 2rem);
            font-weight: 800;
            color: #123f75;
            line-height: 1;
        }

        .metric .t {
            font-size: 13px;
            color: var(--muted);
        }

        .summary {
            margin-bottom: 16px;
            padding: 12px;
            border-radius: 12px;
            background: #e9f6f4;
            border-left: 4px solid var(--brand);
            color: #0f3f4d;
        }

        .charts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .chart-card {
            border-radius: 16px;
            border: 1px solid var(--line);
            background: #ffffff;
            padding: 12px;
            box-shadow: 0 8px 20px rgba(25, 35, 60, 0.05);
            height: 320px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .chart-card canvas {
            width: 100% !important;
            height: 240px !important;
            max-height: 240px;
        }

        .chart-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 28px rgba(25, 35, 60, 0.12);
        }

        .chart-card h3 {
            margin: 0 0 10px;
            font-family: "Sora", sans-serif;
            font-size: 0.98rem;
        }

        .table-wrap {
            overflow: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
            background: #ffffff;
        }

        th,
        td {
            border: 1px solid #e4e9f3;
            padding: 8px;
            font-size: 13px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #45536d;
        }

        h2 {
            margin: 0 0 10px;
            font-family: "Sora", sans-serif;
            font-size: 1.05rem;
        }

        .source-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 16px;
        }

        .pill {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 12px;
            background: #e6eefb;
            color: #1d4e89;
            margin-top: 4px;
        }

        .muted {
            color: var(--muted);
            font-size: 13px;
        }

        .small {
            font-size: 12px;
            color: var(--muted);
        }

        .local-analyzer {
            margin-top: 16px;
            border: 1px dashed #b8c5de;
            background: rgba(255, 255, 255, 0.78);
            border-radius: 16px;
            padding: 14px;
        }

        .local-head {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: end;
            margin-bottom: 10px;
        }

        .local-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }

        .local-card {
            border: 1px solid #dbe3f2;
            border-radius: 12px;
            background: #ffffff;
            padding: 10px;
        }

        .local-title {
            font-size: 12px;
            color: var(--muted);
        }

        .local-value {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1d4578;
        }

        .notice {
            margin-bottom: 16px;
            border-radius: 12px;
            padding: 10px 12px;
            border-left: 4px solid var(--warn);
            background: #fff4e8;
            color: #8a3513;
            font-size: 13px;
        }

        .busy-overlay {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(12, 16, 28, 0.52);
            backdrop-filter: blur(4px);
            z-index: 9999;
        }

        .busy-overlay.show {
            display: flex;
        }

        .busy-card {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #d8dfef;
            border-radius: 18px;
            padding: 18px 20px;
            width: min(92vw, 360px);
            text-align: center;
            box-shadow: 0 20px 40px rgba(14, 26, 48, 0.2);
        }

        .spinner {
            width: 52px;
            height: 52px;
            margin: 0 auto 10px;
            border-radius: 50%;
            border: 4px solid #d7def0;
            border-top-color: var(--brand-3);
            border-right-color: var(--brand);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes drift {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        a {
            color: #115f9f;
        }

        @media (max-width: 960px) {
            .tool-grid,
            .source-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <section class="hero">
            <h1>Observatorio de Aspirantes a Media Superior</h1>
            <p>Analiza directamente BD_SUSTENTANTES_2024.xlsx desde tu servidor y explora patrones de equidad, desempeno y acceso digital para decisiones educativas con evidencia.</p>
        </section>

        <?php if ($error !== null): ?>
            <div class="status error">
                <strong>Error de lectura:</strong> <?= h($error) ?>
                <div class="small" style="margin-top:6px;">Asegurate de tener extensiones ZipArchive y XMLReader habilitadas para XLSX.</div>
                <div class="small" style="margin-top:4px;">Mientras tanto puedes usar la seccion "Analisis local rapido" para obtener resultados sin depender de extensiones del servidor.</div>
            </div>
        <?php endif; ?>

        <section class="panel mb16">
            <h2>Fuente de datos activa</h2>
            <div class="muted">Archivo actual: <strong><?= h($activeFile !== null ? basename($activeFile) : 'No encontrado') ?></strong></div>
            <div class="pill"><?= h($activeFile !== null && is_file($activeFile) ? fileSizeLabel((int) filesize($activeFile)) : '0 B') ?></div>
            <div class="small" style="margin-top:6px;">Ruta usada por el sistema: <?= h($activeFile !== null ? $activeFile : 'Sin ruta valida') ?></div>
            <div class="small" style="margin-top:4px;">Se busca en webapp/BD_SUSTENTANTES_2024.xlsx, webapp/../BD_SUSTENTANTES_2024.xlsx y tambien en CSV.</div>
            <div class="small" style="margin-top:4px;">Si falta ZipArchive para XLSX, el sistema intenta automaticamente BD_SUSTENTANTES_2024.csv en la misma ruta.</div>
        </section>

        <section class="panel mb16">
            <h2>Filtros anidados</h2>
            <form class="filters js-busy-form" method="get" action="">
                <div>
                    <label for="municipio">Municipio contiene</label>
                    <input id="municipio" name="municipio" type="text" value="<?= h($filters['municipio']) ?>" />
                </div>
                <div>
                    <label for="promedio_min">Promedio minimo (&gt;)</label>
                    <input id="promedio_min" name="promedio_min" type="number" step="0.01" value="<?= h($filters['promedio_min']) ?>" />
                </div>
                <div>
                    <label for="sin_internet">Sin internet</label>
                    <select id="sin_internet" name="sin_internet">
                        <option value="" <?= $filters['sin_internet'] === '' ? 'selected' : '' ?>>No aplicar</option>
                        <option value="1" <?= $filters['sin_internet'] === '1' ? 'selected' : '' ?>>Si</option>
                    </select>
                </div>
                <div>
                    <label for="sexo">Sexo</label>
                    <select id="sexo" name="sexo">
                        <option value="" <?= $filters['sexo'] === '' ? 'selected' : '' ?>>No aplicar</option>
                        <option value="F" <?= $filters['sexo'] === 'F' ? 'selected' : '' ?>>F</option>
                        <option value="M" <?= $filters['sexo'] === 'M' ? 'selected' : '' ?>>M</option>
                    </select>
                </div>
                <div>
                    <label for="nivel_socioeconomico">Nivel socioeconomico</label>
                    <select id="nivel_socioeconomico" name="nivel_socioeconomico">
                        <option value="" <?= $filters['nivel_socioeconomico'] === '' ? 'selected' : '' ?>>No aplicar</option>
                        <option value="Muy bajo" <?= $filters['nivel_socioeconomico'] === 'Muy bajo' ? 'selected' : '' ?>>Muy bajo</option>
                        <option value="Bajo" <?= $filters['nivel_socioeconomico'] === 'Bajo' ? 'selected' : '' ?>>Bajo</option>
                        <option value="Medio" <?= $filters['nivel_socioeconomico'] === 'Medio' ? 'selected' : '' ?>>Medio</option>
                        <option value="Alto" <?= $filters['nivel_socioeconomico'] === 'Alto' ? 'selected' : '' ?>>Alto</option>
                    </select>
                </div>
                <div>
                    <label for="seguridad_alimentaria">Seguridad alimentaria</label>
                    <select id="seguridad_alimentaria" name="seguridad_alimentaria">
                        <option value="" <?= $filters['seguridad_alimentaria'] === '' ? 'selected' : '' ?>>No aplicar</option>
                        <option value="Seguridad adecuada" <?= $filters['seguridad_alimentaria'] === 'Seguridad adecuada' ? 'selected' : '' ?>>Seguridad adecuada</option>
                        <option value="Inseguridad moderada" <?= $filters['seguridad_alimentaria'] === 'Inseguridad moderada' ? 'selected' : '' ?>>Inseguridad moderada</option>
                        <option value="Inseguridad severa" <?= $filters['seguridad_alimentaria'] === 'Inseguridad severa' ? 'selected' : '' ?>>Inseguridad severa</option>
                        <option value="Revisar catalogo" <?= $filters['seguridad_alimentaria'] === 'Revisar catalogo' ? 'selected' : '' ?>>Revisar catalogo</option>
                    </select>
                </div>
                <div>
                    <label>Logica aplicada</label>
                    <input type="text" value="Todos los filtros seleccionados se cumplen al mismo tiempo" disabled />
                </div>
                <div style="display:flex; align-items:flex-end;">
                    <button class="btn" type="submit">Aplicar filtros</button>
                </div>
            </form>
        </section>

        <section class="summary">
            <strong>Resumen:</strong> <?= h($result['resumen']) ?>
        </section>

        <?php if ($result['notice'] !== ''): ?>
            <section class="notice">
                <strong>Modo alto rendimiento:</strong> <?= h($result['notice']) ?>
            </section>
        <?php endif; ?>

        <section class="stats">
            <article class="metric">
                <div class="t">Registros totales</div>
                <div class="k"><?= (int) $result['total_registros'] ?></div>
            </article>
            <article class="metric">
                <div class="t">Registros filtrados</div>
                <div class="k"><?= (int) $result['registros_filtrados'] ?></div>
            </article>
            <article class="metric">
                <div class="t">Promedio global (filtrado)</div>
                <div class="k"><?= $result['promedio_global_filtrado'] !== null ? h($result['promedio_global_filtrado']) : 'N/D' ?></div>
            </article>
            <article class="metric">
                <div class="t">Archivo activo</div>
                <div class="k" style="font-size:1rem; line-height:1.4;"><?= h($activeFile !== null ? basename($activeFile) : 'Sin archivo activo') ?></div>
            </article>
        </section>

        <section class="charts">
            <article class="chart-card">
                <h3>Genero (registros filtrados)</h3>
                <canvas id="chartGender"></canvas>
            </article>
            <article class="chart-card">
                <h3>Desempeno academico</h3>
                <canvas id="chartAcademic"></canvas>
            </article>
            <article class="chart-card">
                <h3>Nivel socioeconomico</h3>
                <canvas id="chartSocio"></canvas>
            </article>
            <article class="chart-card">
                <h3>Seguridad alimentaria</h3>
                <canvas id="chartFood"></canvas>
            </article>
            <article class="chart-card">
                <h3>Conectividad digital</h3>
                <canvas id="chartInternet"></canvas>
            </article>
            <article class="chart-card">
                <h3>Top zonas por promedio</h3>
                <canvas id="chartZones"></canvas>
            </article>
        </section>

        <section class="panel mb16">
            <h2>Top 10 municipios por volumen total</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Municipio</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['top_municipios'] as $mun => $qty): ?>
                            <tr>
                                <td><?= h($mun) ?></td>
                                <td><?= (int) $qty ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <h2>Resultados encontrados (vista previa)</h2>
            <p class="muted">Se muestran hasta <?= (int) MAX_ROWS_PREVIEW ?> filas para evitar sobrecarga visual.</p>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>SEXO</th>
                            <th>PROMEDIO</th>
                            <th>ALCMUN_ASP</th>
                            <th>ING_MEN</th>
                            <th>REC_ALI</th>
                            <th>COMPU</th>
                            <th>Nivel socioeconomico</th>
                            <th>Desempeno academico</th>
                            <th>Ubicacion geografica</th>
                            <th>Seguridad alimentaria</th>
                            <th>Equipamiento tecnologico</th>
                            <th>Internet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['preview'] as $r): ?>
                            <tr>
                                <td><?= h(showOrColumn($r['SEXO'], 'SEXO')) ?></td>
                                <td><?= h(showOrColumn($r['PROMEDIO'], 'PROMEDIO')) ?></td>
                                <td><?= h(showOrColumn($r['ALCMUN_ASP'], 'ALCMUN_ASP')) ?></td>
                                <td><?= h(showOrColumn($r['ING_MEN'], 'ING_MEN')) ?></td>
                                <td><?= h(showOrColumn($r['REC_ALI'], 'REC_ALI')) ?></td>
                                <td><?= h(showOrColumn($r['COMPU'], 'COMPU/BIEN_PC')) ?></td>
                                <td><?= h($r['nivel_socioeconomico']) ?></td>
                                <td><?= h($r['desempeno_academico']) ?></td>
                                <td><?= h($r['ubicacion_geografica']) ?></td>
                                <td><?= h($r['seguridad_alimentaria']) ?></td>
                                <td><?= h($r['equipamiento_tecnologico']) ?></td>
                                <td><?= h(showOrColumn($r['acceso_internet'], 'SER_INTE')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="source-grid">
            <article class="panel">
                <h2>Fuente de datos y quienes son</h2>
                <p class="muted">Estos datos se gestionan desde el repositorio y formulario de acceso de XABER en <a href="https://xaber.org.mx/descargas/" target="_blank" rel="noopener noreferrer">xaber.org.mx/descargas</a>.</p>
                <p class="muted">Con base en su seccion "Quienes somos", XABER es una organizacion de la sociedad civil sin fines de lucro enfocada en mejorar aprendizaje y equidad educativa mediante evidencia, investigacion aplicada y colaboracion intersectorial.</p>
                <p class="muted">Mas informacion institucional: <a href="https://xaber.org.mx/quienes-somos/" target="_blank" rel="noopener noreferrer">xaber.org.mx/quienes-somos</a>.</p>
            </article>

            <article class="panel">
                <h2>Objetivo del proyecto y posibles clientes</h2>
                <p class="muted"><strong>Objetivo:</strong> transformar bases crudas de aspirantes a media superior en tableros accionables para detectar brechas de desempeno, riesgo social y acceso tecnologico, apoyando decisiones de focalizacion educativa.</p>
                <p class="muted"><strong>A quienes podria venderse:</strong></p>
                <ul class="muted" style="margin-top:0;">
                    <li>Secretarias de Educacion estatales y municipalidades.</li>
                    <li>Subsistemas de media superior y bachilleratos publicos/privados.</li>
                    <li>ONGs y fundaciones educativas con enfoque en equidad.</li>
                    <li>Consultoras de politica publica y laboratorios de innovacion educativa.</li>
                    <li>Universidades e institutos de investigacion educativa.</li>
                </ul>
            </article>
        </section>

        <section class="local-analyzer">
            <h2>Analisis local rapido (sin extensiones del servidor)</h2>
            <p class="muted">Selecciona tu XLSX o CSV aqui mismo. Se procesa en tu navegador con muestreo para evitar que Chrome se congele.</p>
            <div class="local-head">
                <div style="min-width:260px; flex:1;">
                    <label for="localFileInput">Archivo local</label>
                    <input id="localFileInput" type="file" accept=".xlsx,.csv" />
                </div>
                <div style="min-width:180px;">
                    <label for="localSampleLimit">Max filas a analizar</label>
                    <select id="localSampleLimit">
                        <option value="1000">1000</option>
                        <option value="3000" selected>3000</option>
                        <option value="5000">5000</option>
                        <option value="8000">8000</option>
                    </select>
                </div>
                <div>
                    <button id="runLocalAnalysis" class="btn" type="button">Analizar archivo local</button>
                </div>
            </div>
            <div id="localStatus" class="small" style="margin-bottom:10px;">Sin analisis local ejecutado.</div>
            <div id="localResults" style="display:none;">
                <div class="local-grid">
                    <div class="local-card"><div class="local-title">Registros analizados</div><div id="lTotal" class="local-value">0</div></div>
                    <div class="local-card"><div class="local-title">Promedio general</div><div id="lAvg" class="local-value">0</div></div>
                    <div class="local-card"><div class="local-title">Con internet</div><div id="lInternetYes" class="local-value">0</div></div>
                    <div class="local-card"><div class="local-title">Sin internet</div><div id="lInternetNo" class="local-value">0</div></div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>SEXO</th>
                                <th>PROMEDIO</th>
                                <th>ALCMUN_ASP</th>
                                <th>ING_MEN</th>
                                <th>REC_ALI</th>
                                <th>COMPU</th>
                            </tr>
                        </thead>
                        <tbody id="localPreviewBody"></tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div id="busyOverlay" class="busy-overlay" aria-live="polite" aria-busy="false">
        <div class="busy-card">
            <div class="spinner"></div>
            <div style="font-weight:800; margin-bottom:6px;">Procesando archivo grande</div>
            <div class="small">Estamos analizando el dataset. Esto puede tardar un poco en archivos pesados.</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script>
        (function () {
            var busyOverlay = document.getElementById('busyOverlay');
            var forms = document.querySelectorAll('.js-busy-form');
            forms.forEach(function (form) {
                form.addEventListener('submit', function () {
                    if (busyOverlay) {
                        busyOverlay.classList.add('show');
                        busyOverlay.setAttribute('aria-busy', 'true');
                    }
                });
            });

            if (typeof Chart === 'undefined') {
                return;
            }

            var palette = ['#0f7b6d', '#2d4f88', '#f47e5a', '#8b5cf6', '#0ea5e9', '#22c55e', '#f59e0b', '#ef4444'];

            var chartGenderLabels = <?= json_encode($chartGenderLabels) ?>;
            var chartGenderValues = <?= json_encode($chartGenderValues) ?>;
            var chartAcademicLabels = <?= json_encode($chartAcademicLabels) ?>;
            var chartAcademicValues = <?= json_encode($chartAcademicValues) ?>;
            var chartSocioLabels = <?= json_encode($chartSocioLabels) ?>;
            var chartSocioValues = <?= json_encode($chartSocioValues) ?>;
            var chartFoodLabels = <?= json_encode($chartFoodLabels) ?>;
            var chartFoodValues = <?= json_encode($chartFoodValues) ?>;
            var chartInternetLabels = <?= json_encode($chartInternetLabels) ?>;
            var chartInternetValues = <?= json_encode($chartInternetValues) ?>;
            var chartZonesLabels = <?= json_encode($chartZonesLabels) ?>;
            var chartZonesValues = <?= json_encode($chartZonesValues) ?>;

            function create(id, type, labels, values, extraOptions) {
                var canvas = document.getElementById(id);
                if (!canvas) {
                    return;
                }

                new Chart(canvas, {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: palette,
                            borderColor: '#ffffff',
                            borderWidth: 2,
                            borderRadius: type === 'bar' ? 8 : 0
                        }]
                    },
                    options: Object.assign({
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        normalized: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    usePointStyle: true
                                }
                            }
                        },
                        scales: type === 'bar' ? {
                            y: { beginAtZero: true, ticks: { precision: 0, maxTicksLimit: 8 } },
                            x: { ticks: { maxTicksLimit: 8 } }
                        } : {}
                    }, extraOptions || {})
                });
            }

            create('chartGender', 'doughnut', chartGenderLabels, chartGenderValues);
            create('chartAcademic', 'bar', chartAcademicLabels, chartAcademicValues);
            create('chartSocio', 'polarArea', chartSocioLabels, chartSocioValues, {
                scales: { r: { beginAtZero: true, ticks: { precision: 0 } } }
            });
            create('chartFood', 'bar', chartFoodLabels, chartFoodValues);
            create('chartInternet', 'pie', chartInternetLabels, chartInternetValues);
            create('chartZones', 'bar', chartZonesLabels, chartZonesValues, {
                indexAxis: 'y',
                scales: {
                    x: { beginAtZero: true },
                    y: { ticks: { autoSkip: false } }
                }
            });

            function toNum(value) {
                if (value === null || value === undefined) {
                    return null;
                }
                var n = String(value).replace(',', '.').trim();
                if (n === '' || isNaN(Number(n))) {
                    return null;
                }
                return Number(n);
            }

            function toUpper(v) {
                return String(v || '').toUpperCase().trim();
            }

            function pickByAliases(row, aliases) {
                for (var i = 0; i < aliases.length; i++) {
                    var alias = aliases[i];
                    if (row[alias] !== undefined && String(row[alias]).trim() !== '') {
                        return row[alias];
                    }

                    var key = Object.keys(row).find(function (k) { return String(k).toUpperCase().trim() === alias; });
                    if (key && String(row[key]).trim() !== '') {
                        return row[key];
                    }
                }
                return '';
            }

            function safeShow(value, columnName) {
                var txt = String(value === undefined || value === null ? '' : value).trim();
                if (!txt || txt.toLowerCase() === 'undefined' || txt.toLowerCase() === 'null') {
                    return '[' + columnName + ']';
                }
                return txt;
            }

            function hasInternetLocal(row) {
                var internet = toUpper(pickByAliases(row, ['INTERNET', 'SER_INTE', 'SER_CABL', 'SER_TABL']));
                var compu = toUpper(pickByAliases(row, ['COMPU', 'BIEN_PC', 'SER_TABL']));
                if (internet) {
                    if (/^1$/.test(internet)) return true;
                    if (/^2$|^0$/.test(internet)) return false;
                    if (/NO|SIN|0/.test(internet)) return false;
                    if (/SI|S[IÍ]|1/.test(internet)) return true;
                }
                if (/SIN INTERNET|NO INTERNET/.test(compu)) return false;
                return /INTERNET|WIFI/.test(compu);
            }

            var runBtn = document.getElementById('runLocalAnalysis');
            var localInput = document.getElementById('localFileInput');
            var localStatus = document.getElementById('localStatus');
            var localResults = document.getElementById('localResults');
            var localPreviewBody = document.getElementById('localPreviewBody');

            if (runBtn && localInput) {
                runBtn.addEventListener('click', function () {
                    var file = localInput.files && localInput.files[0] ? localInput.files[0] : null;
                    if (!file) {
                        localStatus.textContent = 'Selecciona un archivo primero.';
                        return;
                    }

                    if (typeof XLSX === 'undefined') {
                        localStatus.textContent = 'No se pudo cargar la libreria local de lectura XLSX.';
                        return;
                    }

                    localStatus.textContent = 'Procesando archivo local, espera unos segundos...';
                    localResults.style.display = 'none';
                    localPreviewBody.innerHTML = '';

                    var maxRows = Number(document.getElementById('localSampleLimit').value || 3000);
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        try {
                            var data = new Uint8Array(e.target.result);
                            var wb = XLSX.read(data, { type: 'array' });
                            var sheetName = wb.SheetNames[0];
                            var ws = wb.Sheets[sheetName];
                            var rows = XLSX.utils.sheet_to_json(ws, { defval: '' });

                            if (!rows.length) {
                                localStatus.textContent = 'El archivo no contiene filas legibles.';
                                return;
                            }

                            var step = Math.max(1, Math.floor(rows.length / maxRows));
                            var sampled = [];
                            for (var i = 0; i < rows.length && sampled.length < maxRows; i += step) {
                                sampled.push(rows[i]);
                            }

                            var sum = 0;
                            var count = 0;
                            var internetYes = 0;
                            var internetNo = 0;

                            sampled.forEach(function (r) {
                                var prom = toNum(pickByAliases(r, ['PROMEDIO']));
                                if (prom !== null) {
                                    sum += prom;
                                    count += 1;
                                }
                                if (hasInternetLocal(r)) {
                                    internetYes += 1;
                                } else {
                                    internetNo += 1;
                                }
                            });

                            document.getElementById('lTotal').textContent = String(sampled.length);
                            document.getElementById('lAvg').textContent = count ? (sum / count).toFixed(2) : 'N/D';
                            document.getElementById('lInternetYes').textContent = String(internetYes);
                            document.getElementById('lInternetNo').textContent = String(internetNo);

                            sampled.slice(0, 40).forEach(function (r) {
                                var tr = document.createElement('tr');
                                var cols = [
                                    ['SEXO'],
                                    ['PROMEDIO'],
                                    ['ALCMUN_ASP', 'MUNALC_ESC', 'COLONIA'],
                                    ['ING_MEN'],
                                    ['REC_ALI', 'REC_HAM', 'REC_DCC'],
                                    ['COMPU', 'BIEN_PC', 'SER_TABL']
                                ];
                                cols.forEach(function (aliases) {
                                    var td = document.createElement('td');
                                    td.textContent = safeShow(pickByAliases(r, aliases), aliases[0]);
                                    tr.appendChild(td);
                                });
                                localPreviewBody.appendChild(tr);
                            });

                            localResults.style.display = 'block';
                            localStatus.textContent = 'Analisis local completado: ' + sampled.length + ' filas muestreadas de ' + rows.length + ' totales.';
                        } catch (err) {
                            localStatus.textContent = 'No se pudo leer el archivo local: ' + (err && err.message ? err.message : String(err));
                        }
                    };

                    reader.onerror = function () {
                        localStatus.textContent = 'Error al leer el archivo seleccionado.';
                    };

                    reader.readAsArrayBuffer(file);
                });
            }
        })();
    </script>
</body>
</html>
