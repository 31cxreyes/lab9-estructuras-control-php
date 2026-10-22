<?php
/*
 * AUTOGRADE · Laboratorio #9 · Estructuras de control en PHP
 * Uso:  php autograde.php
 * No guarda nada en servidores externos. Escribe resultado_autograde.json (local).
 */

const ARCHIVO = 'index.php';
const SEMILLAS_PRUEBA = [0, 3, 7, 17, 58, 99, 137, 250, 333, 427, 480, 612, 777, 913, 999];
const FUNCIONES_PERMITIDAS = ['number_format', 'gettype', 'var_dump', 'round', 'define'];
const MIN_SPAN_MIN = 25;

$dir = __DIR__;
$ruta = $dir . '/' . ARCHIVO;
$puntos = [];      // clave => [obtenido, maximo, detalle[]]
$banderas = [];    // [severidad, texto]
$notas = [];

function pts(string $k, float $max, float $obt, array $det = []): void {
    global $puntos;
    $puntos[$k] = [round($obt, 2), $max, $det];
}
function bandera(string $sev, string $txt): void {
    global $banderas;
    $banderas[] = [$sev, $txt];
}

// ================== UTILIDADES ==================
function normalizar(string $s): string {
    return trim(preg_replace('/\s+/u', ' ', $s));
}

function igual(string $esp, string $obt): bool {
    $re = '/(-?\d+(?:\.\d+)?)/';
    $a = preg_split($re, normalizar($esp), -1, PREG_SPLIT_DELIM_CAPTURE);
    $b = preg_split($re, normalizar($obt), -1, PREG_SPLIT_DELIM_CAPTURE);
    if (count($a) !== count($b)) return false;
    foreach ($a as $i => $x) {
        if ($i % 2 === 1) {
            if (abs((float)$x - (float)$b[$i]) > 0.011) return false;
        } elseif ($x !== $b[$i]) {
            return false;
        }
    }
    return true;
}

function slug(string $s): string {
    $s = mb_strtolower($s, 'UTF-8');
    $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    return trim(preg_replace('/[^a-z0-9]+/', '-', $s), '-');
}

function leerConst(string $src, string $nombre) {
    $re = '/(?:const\s+' . $nombre . '\s*=|define\(\s*[\'"]' . $nombre . '[\'"]\s*,)\s*(?:(\d+)|([\'"])(.*?)\2)/u';
    if (preg_match($re, $src, $m)) {
        return ($m[1] !== '') ? (int)$m[1] : $m[3];
    }
    return null;
}

function cambiarSemilla(string $src, int $s): ?string {
    $re = '/((?:const\s+SEMILLA\s*=|define\(\s*[\'"]SEMILLA[\'"]\s*,)\s*)\d+/';
    $r = preg_replace($re, '${1}' . $s, $src, 1, $n);
    return $n === 1 ? $r : null;
}

function ejecutarPhp(string $codigo): string {
    $tmp = tempnam(sys_get_temp_dir(), 'lab9_') . '.php';
    file_put_contents($tmp, $codigo);
    $cmd = escapeshellarg(PHP_BINARY) . ' -d display_errors=1 -d error_reporting=E_ALL ' . escapeshellarg($tmp);
    $p = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($p)) { @unlink($tmp); return ''; }
    stream_set_blocking($pipes[1], false);
    $out = '';
    $ini = microtime(true);
    while (true) {
        $out .= stream_get_contents($pipes[1]);
        $st = proc_get_status($p);
        if (!$st['running']) { $out .= stream_get_contents($pipes[1]); break; }
        if (microtime(true) - $ini > 10) { proc_terminate($p, 9); $out .= "\nFatal error: tiempo excedido"; break; }
        usleep(10000);
    }
    fclose($pipes[1]); fclose($pipes[2]); proc_close($p);
    @unlink($tmp);
    return $out;
}

function dom(string $html): DOMXPath {
    libxml_use_internal_errors(true);
    $d = new DOMDocument();
    $d->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    return new DOMXPath($d);
}

function txt(DOMXPath $x, string $id): ?string {
    $n = $x->query("//*[@id='$id']");
    return $n->length ? normalizar($n->item(0)->textContent) : null;
}

// ================== VALORES ESPERADOS ==================
function esperado(int $S): array {
    $edad = 8 + ($S % 70);
    $dia = ($S % 7) + 1;
    $mes = ($S % 12) + 1;
    $n1 = 40 + ($S % 61);
    $n2 = 40 + (($S * 3) % 61);
    $n3 = 40 + (($S * 7) % 61);
    $monto = 20 + ($S % 181);
    $miembro = ($S % 2) == 0;
    $hora = $S % 24;

    if ($edad < 12) { $cat = 'Niño'; $p = 3.00; }
    elseif ($edad < 18) { $cat = 'Joven'; $p = 4.50; }
    elseif ($edad < 60) { $cat = 'Adulto'; $p = 7.00; }
    else { $cat = 'Jubilado'; $p = 3.50; }
    $mier = ($dia == 3);
    if ($mier) $p *= 0.5;

    $prom = ($n1 + $n2 + $n3) / 3;
    $letra = $prom >= 90 ? 'A' : ($prom >= 80 ? 'B' : ($prom >= 70 ? 'C' : ($prom >= 60 ? 'D' : 'F')));
    $aprob = $prom >= 70;
    $honor = ($prom >= 90 && $n1 >= 80 && $n2 >= 80 && $n3 >= 80);

    $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    $meses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio',
              8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
    $seca = in_array($mes, [12, 1, 2, 3, 4]);

    $pct = $monto >= 150 ? 15 : ($monto >= 100 ? 10 : ($monto >= 50 ? 5 : 0));
    if ($miembro) $pct += 5;
    $base = $monto - ($monto * $pct / 100);
    $noct = ($hora >= 22 || $hora < 6);
    if ($noct) $base += $base * 0.03;
    $total = $base + $base * 0.07;

    return [
        'ej1-res' => "Categoría: $cat | Precio final: B/. " . number_format($p, 2) . ' | Miércoles 50%: ' . ($mier ? 'Sí' : 'No'),
        'ej2-res' => 'Promedio: ' . number_format($prom, 2) . " | Letra: $letra | Estado: " . ($aprob ? 'Aprobado' : 'Reprobado') . ' | Cuadro de honor: ' . ($honor ? 'Sí' : 'No'),
        'ej2-clase' => $aprob ? 'ok' : 'error',
        'ej3-res' => 'Día: ' . $dias[$dia] . ' | Tipo: ' . ($dia <= 5 ? 'Laboral' : 'Fin de semana'),
        'ej4-res' => 'Mes: ' . $meses[$mes] . ' | Estación: ' . ($seca ? 'Seca' : 'Lluviosa'),
        'ej5-monto' => 'Monto de compra: B/. ' . number_format($monto, 2),
        'ej5-descuento' => "Descuento aplicado: $pct%",
        'ej5-recargo' => 'Recargo nocturno: ' . ($noct ? 'Sí' : 'No'),
        'ej5-total' => 'Total a pagar: B/. ' . number_format($total, 2),
        'huella' => (string)(($S * 13 + 91) % 1000),
    ];
}

// ================== INICIO ==================
if (!is_file($ruta)) {
    echo "ERROR: no se encontró " . ARCHIVO . "\n";
    exit(1);
}
$src = file_get_contents($ruta);
$SEM = leerConst($src, 'SEMILLA');
$NOM = leerConst($src, 'NOMBRE');
$APE = leerConst($src, 'APELLIDO');

if (!is_int($SEM) || !is_string($NOM) || !is_string($APE) || trim($NOM) === '' || trim($APE) === '') {
    $notas[] = 'No se pudieron leer las constantes SEMILLA, NOMBRE y APELLIDO. Revisa la Parte 0.';
    $SEM = $SEM ?? 0;
}
$nombreCompleto = trim(($NOM ?? '') . ' ' . ($APE ?? ''));

// ---------- Análisis de tokens ----------
$tokens = token_get_all($src);
$cuenta = [];
$prohibidos = [];
$noPermitidas = [];
$lista = ['T_FOR', 'T_FOREACH', 'T_WHILE', 'T_DO', 'T_FUNCTION', 'T_FN', 'T_CLASS', 'T_INTERFACE', 'T_TRAIT',
          'T_ENUM', 'T_NAMESPACE', 'T_TRY', 'T_THROW', 'T_INCLUDE', 'T_INCLUDE_ONCE', 'T_REQUIRE',
          'T_REQUIRE_ONCE', 'T_GOTO', 'T_NULLSAFE_OBJECT_OPERATOR'];
$idsProh = [];
foreach ($lista as $nm) if (defined($nm)) $idsProh[constant($nm)] = $nm;
$firmaTokens = '';
$n = count($tokens);
$cod = [];
for ($i = 0; $i < $n; $i++) {
    $t = $tokens[$i];
    if (!is_array($t)) { $cod[] = $t; continue; }
    [$id, $val] = $t;
    if (in_array($id, [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT, T_INLINE_HTML, T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG], true)) continue;
    $nm = token_name($id);
    $cuenta[$nm] = ($cuenta[$nm] ?? 0) + 1;
    $cod[] = $t;
    if (isset($idsProh[$id])) $prohibidos[$idsProh[$id]] = ($prohibidos[$idsProh[$id]] ?? 0) + 1;
    if ($id === T_VARIABLE && in_array($val, ['$_GET', '$_POST', '$_REQUEST', '$_SESSION', '$_COOKIE', '$GLOBALS'], true)) {
        $prohibidos[$val] = ($prohibidos[$val] ?? 0) + 1;
    }
    if (defined('T_COALESCE') && $id === T_COALESCE) $prohibidos['??'] = 1;
    if (defined('T_SPACESHIP') && $id === T_SPACESHIP) $prohibidos['<=>'] = 1;
}
// ternario, else if, funciones llamadas, firma
$ternarios = 0; $elseif = $cuenta['T_ELSEIF'] ?? 0;
$m = count($cod);
for ($i = 0; $i < $m; $i++) {
    $t = $cod[$i];
    if ($t === '?') $ternarios++;
    if (is_array($t)) {
        if ($t[0] === T_ELSE && isset($cod[$i + 1]) && is_array($cod[$i + 1]) && $cod[$i + 1][0] === T_IF) $elseif++;
        if ($t[0] === T_STRING && ($cod[$i + 1] ?? null) === '(') {
            $prev = $cod[$i - 1] ?? null;
            $esMetodo = is_array($prev) && in_array($prev[0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NEW, T_FUNCTION], true);
            if (!$esMetodo && !in_array(strtolower($t[1]), FUNCIONES_PERMITIDAS, true)) {
                $noPermitidas[strtolower($t[1])] = true;
            }
        }
        if (in_array($t[0], [T_VARIABLE, T_STRING], true)) $firmaTokens .= $t[1] . ';';
        elseif (!in_array($t[0], [T_LNUMBER, T_DNUMBER, T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE], true)) $firmaTokens .= token_name($t[0]) . ';';
    } else {
        $firmaTokens .= $t;
    }
}
$c = fn(string $k) => $cuenta[$k] ?? 0;

// ---------- Ejecuciones ----------
$salidaPropia = ejecutarPhp($src);
$fatal = (bool)preg_match('/(Parse error|Fatal error)/i', $salidaPropia);
if ($fatal) $notas[] = 'Tu index.php produce un error fatal / de sintaxis. Corrígelo para obtener puntos.';
if (preg_match_all('/(Warning|Notice|Deprecated):/i', $salidaPropia, $mm)) {
    $notas[] = 'PHP muestra ' . count($mm[0]) . ' advertencia(s) (Warning/Notice). Revisa variables no definidas.';
}

$semillas = array_values(array_unique(array_merge([$SEM], SEMILLAS_PRUEBA)));
$resultados = [];   // semilla => [id => bool]
foreach ($semillas as $s) {
    $codigo = ($s === $SEM) ? $src : cambiarSemilla($src, $s);
    if ($codigo === null) { $resultados[$s] = null; continue; }
    $out = ($s === $SEM) ? $salidaPropia : ejecutarPhp($codigo);
    $x = dom($out);
    $esp = esperado($s);
    $r = [];
    $r['ej1'] = ($t = txt($x, 'ej1-res')) !== null && igual($esp['ej1-res'], $t);
    $r['ej2'] = ($t = txt($x, 'ej2-res')) !== null && igual($esp['ej2-res'], $t);
    $nodo = $x->query("//*[@id='ej2-res']");
    $clases = $nodo->length ? preg_split('/\s+/', $nodo->item(0)->getAttribute('class')) : [];
    $otra = $esp['ej2-clase'] === 'ok' ? 'error' : 'ok';
    $r['clase'] = in_array($esp['ej2-clase'], $clases, true) && !in_array($otra, $clases, true);
    $r['ej3'] = ($t = txt($x, 'ej3-res')) !== null && igual($esp['ej3-res'], $t);
    $r['ej4'] = ($t = txt($x, 'ej4-res')) !== null && igual($esp['ej4-res'], $t);
    $ok5 = true;
    foreach (['ej5-monto', 'ej5-descuento', 'ej5-recargo', 'ej5-total'] as $id) {
        $t = txt($x, $id);
        if ($t === null || !igual($esp[$id], $t)) $ok5 = false;
    }
    $r['ej5'] = $ok5;
    $mt = $x->query("//meta[@name='lab9-huella']");
    $r['huella'] = $mt->length && trim($mt->item(0)->getAttribute('content')) === $esp['huella'];
    $f = txt($x, 'firma');
    $r['firma'] = $f !== null && $nombreCompleto !== '' && str_contains($f, $nombreCompleto) && preg_match('/Semilla\s+' . $s . '\b/', $f);
    $resultados[$s] = $r;
}
function fraccion(array $resultados, string $k, ?int $solo = null, ?int $excepto = null): float {
    $ok = 0; $tot = 0;
    foreach ($resultados as $s => $r) {
        if ($r === null) { $tot++; continue; }
        if ($solo !== null && $s !== $solo) continue;
        if ($excepto !== null && $s === $excepto) continue;
        $tot++;
        if (!empty($r[$k])) $ok++;
    }
    return $tot ? $ok / $tot : 0;
}
if (in_array(null, $resultados, true)) {
    $notas[] = 'SEMILLA debe declararse como número literal: const SEMILLA = 123; (el autograde la cambia para probar tu lógica con otros valores).';
}

// ---------- Estructura HTML5 (7 pts) ----------
$x = dom($salidaPropia);
$h = 0; $det = [];
$chk = function (bool $ok, float $v, string $msg) use (&$h, &$det) { if ($ok) $h += $v; else $det[] = $msg; };
$chk(preg_match('/^\s*<!DOCTYPE html>/i', ltrim($salidaPropia)) === 1 && $x->query('//html[@lang]')->length > 0, 0.75, 'Falta <!DOCTYPE html> o lang en <html>');
$chk($x->query('//meta[@charset]')->length > 0 && trim(txt($x, 'xx') ?? (($t = $x->query('//title')) && $t->length ? $t->item(0)->textContent : '')) !== '', 1.25, 'Falta meta charset o <title>');
$chk($x->query('//header')->length && $x->query('//main')->length && $x->query('//footer')->length, 2, 'Faltan <header>, <main> o <footer>');
$okSec = true; foreach (range(0, 5) as $i) if (!$x->query("//section[@id='ej$i']")->length) $okSec = false;
$chk($okSec, 2, 'Faltan las 6 <section id="ej0"..."ej5">');
$chk($x->query("//table[@id='ej0-tipos']/thead")->length && $x->query("//table[@id='ej0-tipos']/tbody")->length, 1, 'La tabla debe usar <thead> y <tbody>');
pts('HTML5 semántico', 7, $h, $det);

// ---------- CSS (8 pts) ----------
$c8 = 0; $det = [];
$link = $x->query("//link[@rel='stylesheet'][contains(@href,'estilos.css')]")->length > 0;
if ($link) $c8 += 2; else $det[] = 'Falta <link rel="stylesheet" href="css/estilos.css">';
$st = $x->query('//style');
if ($st->length && substr_count($st->item(0)->textContent, '{') >= 2) $c8 += 2; else $det[] = 'Falta <style> interno con al menos 2 reglas';
if ($x->query('//*[@style]')->length > 0) $c8 += 1; else $det[] = 'Falta al menos un atributo style (en línea)';
$css = is_file($dir . '/css/estilos.css') ? file_get_contents($dir . '/css/estilos.css') : '';
if (preg_match('/\.ok\s*\{[^}]+\}/', $css) && preg_match('/\.error\s*\{[^}]+\}/', $css)) $c8 += 1; else $det[] = 'estilos.css debe definir .ok y .error';
$fc = fraccion($resultados, 'clase');
$c8 += 2 * $fc;
if ($fc < 1) $det[] = 'La clase ok/error de #ej2-res no cambia correctamente según el estado (' . round($fc * 100) . '% de casos)';
pts('CSS integrado', 8, $c8, $det);

// ---------- Ejercicio 0 (10 pts) ----------
$e0 = 0; $det = [];
$filas = $x->query("//table[@id='ej0-tipos']/tbody/tr");
$esp0 = [['nombreCompleto', 'string'], ['edad', 'integer'], ['esMiembro', 'boolean'], ['descuento', 'double'], ['vacio', 'NULL']];
if ($filas->length === 5) $e0 += 2; else $det[] = 'La tabla debe tener exactamente 5 filas en <tbody>';
foreach ($esp0 as $i => [$v, $t]) {
    $tds = $filas->length > $i ? $filas->item($i)->getElementsByTagName('td') : null;
    if ($tds && $tds->length >= 2 && ltrim(normalizar($tds->item(0)->textContent), '$') === $v && normalizar($tds->item(1)->textContent) === $t) $e0 += 1.2;
    else $det[] = "Fila " . ($i + 1) . ": se esperaba \$$v → $t";
}
$e0 += 1 * (fraccion($resultados, 'huella') == 1 ? 1 : 0) + 1 * (fraccion($resultados, 'firma') == 1 ? 1 : 0);
if (fraccion($resultados, 'huella') < 1) $det[] = 'La huella (meta lab9-huella) no coincide con la fórmula';
if (fraccion($resultados, 'firma') < 1) $det[] = 'El #firma debe contener "Nombre Apellido" y "Semilla N"';
pts('Ej0 · Datos y tipos', 10, min(10, $e0), $det);

// ---------- Ejercicios 1-5 (70% salida + 30% estructura) ----------
$def = [
    ['ej1', 'Ej1 · if/elseif/else', 12, $elseif >= 2, 'Usa al menos 2 elseif (cadena if / elseif / else)'],
    ['ej2', 'Ej2 · elseif, ternario y &&', 15, $elseif >= 5 && $ternarios >= 1 && $c('T_BOOLEAN_AND') + $c('T_LOGICAL_AND') >= 1, 'Usa cadena de elseif, un operador ternario (? :) y el operador &&'],
    ['ej3', 'Ej3 · switch', 10, $c('T_SWITCH') >= 1 && $c('T_CASE') >= 7 && $c('T_DEFAULT') >= 1, 'Usa switch con al menos 7 case y un default (casos agrupados)'],
    ['ej4', 'Ej4 · match', 10, defined('T_MATCH') && $c('T_MATCH') >= 2, 'Usa match (PHP 8) al menos 2 veces'],
    ['ej5', 'Ej5 · Reto integrador', 18, $c('T_IF') >= 6 && ($c('T_BOOLEAN_OR') + $c('T_LOGICAL_OR')) >= 1, 'Combina if anidados/encadenados y el operador ||'],
];
foreach ($def as [$k, $nombre, $max, $estructura, $msg]) {
    $f = fraccion($resultados, $k);
    $det = [];
    if ($f < 1) $det[] = 'Salida correcta en ' . round($f * 100) . '% de los casos de prueba';
    if (!$estructura) $det[] = 'Estructura requerida: ' . $msg;
    pts($nombre, $max, $max * (0.7 * $f + ($estructura ? 0.3 : 0)), $det);
}

// ---------- Git (10 pts) ----------
$g = 0; $det = []; $commits = [];
$rama = getenv('GITHUB_REF_NAME') ?: trim((string)@shell_exec('git -C ' . escapeshellarg($dir) . ' rev-parse --abbrev-ref HEAD 2>/dev/null'));
$log = @shell_exec('git -C ' . escapeshellarg($dir) . ' log --format="%at|%ae|%s" 2>/dev/null');
if ($log) {
    foreach (explode("\n", trim($log)) as $l) {
        $p = explode('|', $l, 3);
        if (count($p) === 3 && preg_match('/^(setup|ej[0-5]|css|final)\b/i', trim($p[2]), $mm)) {
            $commits[] = ['t' => (int)$p[0], 'e' => $p[1], 'k' => strtolower($mm[1])];
        }
    }
}
if (preg_match('/^lab9-[a-z0-9]+(-[a-z0-9]+)+$/', $rama)) $g += 3; else $det[] = "Nombre de rama '$rama' no cumple lab9-apellido-nombre";
$distintos = count(array_unique(array_column($commits, 'k')));
$g += 4 * min($distintos, 6) / 6;
if ($distintos < 6) $det[] = "Commits con prefijo válido (ej0..ej5, css, final): $distintos de 6 mínimos";
$span = 0;
if (count($commits) >= 2) {
    $ts = array_column($commits, 't');
    $span = (max($ts) - min($ts)) / 60;
}
if ($span >= MIN_SPAN_MIN) $g += 3; else $det[] = 'Tiempo entre tu primer y último commit: ' . round($span) . ' min (mínimo ' . MIN_SPAN_MIN . ')';
if ($log === null || $log === '') $notas[] = 'No se pudo leer el historial de git desde este entorno.';
pts('Git · rama y commits', 10, $g, $det);

// ================== BANDERAS ANTIPLAGIO ==================
if ($prohibidos) {
    bandera('MEDIA', 'Usa estructuras NO vistas en el curso: ' . implode(', ', array_keys($prohibidos)));
}
if ($noPermitidas) {
    bandera('BAJA', 'Llama a funciones no vistas: ' . implode(', ', array_keys($noPermitidas)));
}
$propias = 0; $otras = 0; $nOtras = 0;
foreach (['ej1', 'ej2', 'ej3', 'ej4', 'ej5'] as $k) {
    $propias += fraccion($resultados, $k, $SEM);
    $otras += fraccion($resultados, $k, null, $SEM);
}
if ($propias >= 4 && $otras / 5 < 0.5) {
    bandera('ALTA', 'La salida es correcta con tu semilla pero falla con otras semillas: posibles resultados escritos a mano (hardcode).');
}
if (fraccion($resultados, 'huella') < 1 || !preg_match('/id=["\']lab9-' . preg_quote(slug((string)$APE), '/') . '["\']/', $salidaPropia)) {
    bandera('MEDIA', 'No cumple los detalles de control personales (huella o id de <main> = lab9-apellido).');
}
if (count($commits) <= 2) {
    bandera('ALTA', 'Casi todo el trabajo está en 1-2 commits (no hay historial paso a paso).');
} elseif ($span < MIN_SPAN_MIN) {
    bandera('MEDIA', 'Commits muy juntos en el tiempo (' . round($span) . ' min): el lab debía tomar al menos 60 min.');
}
if (count(array_unique(array_column($commits, 'e'))) > 1) {
    bandera('MEDIA', 'Los commits fueron hechos con más de un correo/autor.');
}
if (getenv('AUTOGRADE_MODIFICADO')) bandera('ALTA', 'autograde.php fue modificado respecto al oficial.');
if (getenv('WORKFLOW_MODIFICADO')) bandera('ALTA', 'El workflow autograde.yml fue modificado respecto al oficial.');

$pes = ['BAJA' => 1, 'MEDIA' => 2, 'ALTA' => 3];
$nivel = 'NINGUNA';
$sum = array_count_values(array_column($banderas, 0));
if (!empty($sum['ALTA']) || ($sum['MEDIA'] ?? 0) >= 3) $nivel = 'ALTA';
elseif (!empty($sum['MEDIA']) || ($sum['BAJA'] ?? 0) >= 3) $nivel = 'MEDIA';
elseif (!empty($sum['BAJA'])) $nivel = 'BAJA';

// ================== REPORTE ==================
$total = 0; $max = 0;
foreach ($puntos as [$o, $m]) { $total += $o; $max += $m; }
$total = (int)round($total);
$firma = substr(sha1($firmaTokens), 0, 12);

$L = [];
$L[] = '=== AUTOGRADE · Laboratorio #9 · Estructuras de control en PHP ===';
$L[] = 'Estudiante: ' . ($nombreCompleto ?: '(sin definir)') . ' | Semilla: ' . $SEM . ' | Rama: ' . ($rama ?: '(desconocida)');
$L[] = 'Fuente del autograde: ' . (getenv('AUTOGRADE_FUENTE') ?: 'local');
$L[] = str_repeat('-', 64);
foreach ($puntos as $nombre => [$o, $m, $d]) {
    $L[] = sprintf('[%s] %-30s %5.1f / %d', $o >= $m - 0.05 ? 'OK' : '..', $nombre, $o, $m);
    foreach ($d as $linea) $L[] = '       - ' . $linea;
}
$L[] = str_repeat('-', 64);
$L[] = "NOTA FINAL: $total / $max";
foreach ($notas as $nt) $L[] = 'NOTA: ' . $nt;
$L[] = "Observación de plagio: $nivel";
foreach ($banderas as [$sv, $tx]) $L[] = "   [$sv] $tx";
$L[] = "Firma de estructura (uso docente): $firma";
$reporte = implode("\n", $L);
echo $reporte . "\n";

file_put_contents($dir . '/resultado_autograde.json', json_encode([
    'estudiante' => $nombreCompleto, 'semilla' => $SEM, 'rama' => $rama, 'nota' => $total, 'maximo' => $max,
    'detalle' => $puntos, 'plagio' => ['nivel' => $nivel, 'banderas' => $banderas], 'firma' => $firma,
    'fecha' => date('c'),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

if ($resumen = getenv('GITHUB_STEP_SUMMARY')) {
    file_put_contents($resumen, "## Autograde Lab 9\n\n```\n$reporte\n```\n", FILE_APPEND);
}
exit(0);
