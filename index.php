<?php

require __DIR__ . '/config.php';

function h($v)

{

    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

}

$sintomas = $pdo->query('SELECT id,nombre,categoria FROM sintomas ORDER BY FIELD(categoria,\'General\',\'Respiratorio\',\'Digestivo y otros\'),nombre')->fetchAll();

$validos = array_map('intval', array_column($sintomas, 'id'));

$seleccion = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['sintomas'] ?? [])), fn($id) => in_array($id, $validos, true))));

$resultados = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $seleccion) {

    $placeholders = implode(',', array_fill(0, count($seleccion), '?'));

    // Puntaje: suma de pesos coincidentes / suma de pesos de todos los síntomas registrados para esa enfermedad.

    // También se exige coincidencia de al menos un síntoma. No representa probabilidad médica.

    $sql = "SELECT * FROM (SELECT e.id,e.nombre, SUM(CASE WHEN es.sintoma_id IN ($placeholders) THEN es.peso ELSE 0 END) AS coincidencia, SUM(es.peso) AS maximo, SUM(CASE WHEN es.sintoma_id IN ($placeholders) THEN 1 ELSE 0 END) AS coincidencias FROM enfermedades e JOIN enfermedad_sintoma es ON es.enfermedad_id=e.id GROUP BY e.id,e.nombre) AS puntuaciones WHERE coincidencia > 0 ORDER BY (coincidencia/maximo) DESC, coincidencia DESC, nombre ASC LIMIT 5";

    $stmt = $pdo->prepare($sql);

    $stmt->execute(array_merge($seleccion, $seleccion));

    $resultados = $stmt->fetchAll();

}

$pagina = $_GET['pagina'] ?? 'inicio';

if (!in_array($pagina, ['inicio', 'sintomas', 'enfermedades', 'referencias'], true))

    $pagina = 'inicio';

$enfermedades = $pagina === 'enfermedades' ? $pdo->query('SELECT e.nombre,s.nombre AS sintoma,es.peso FROM enfermedades e JOIN enfermedad_sintoma es ON e.id=es.enfermedad_id JOIN sintomas s ON s.id=es.sintoma_id ORDER BY e.nombre,es.peso DESC,s.nombre')->fetchAll() : [];

$referencias = $pagina === 'referencias' ? $pdo->query('SELECT * FROM referencias ORDER BY id')->fetchAll() : [];

$catalogo = []; foreach ($enfermedades as $e) $catalogo[$e['nombre']][] = $e;
$alerta = array_intersect($seleccion, [17,19,20]);
$detalles = []; if ($resultados) { $q = $pdo->prepare('SELECT s.nombre, r.url, r.institucion FROM enfermedad_sintoma es JOIN sintomas s ON s.id=es.sintoma_id JOIN enfermedades e ON e.id=es.enfermedad_id JOIN referencias r ON r.id=e.referencia_id WHERE e.id=? AND s.id IN ('.implode(',',array_fill(0,count($seleccion),'?')).')'); foreach($resultados as $r){$q->execute(array_merge([$r['id']],$seleccion)); $detalles[$r['id']]=$q->fetchAll();} }
?>

<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SEPE | Evaluación de síntomas</title><link rel="stylesheet" href="assets/style.css"><script defer src="assets/app.js"></script></head><body>
<header><a class="brand" href="index.php"><span class="logo">✚</span><span>SE<span>PE</span></span></a><nav><?php foreach(['inicio'=>'Inicio','sintomas'=>'Evaluación','enfermedades'=>'Enfermedades','referencias'=>'Referencias'] as $k=>$v): ?><a class="<?= $pagina===$k?'active':'' ?>" href="?pagina=<?= $k ?>"><?= $v ?></a><?php endforeach ?></nav><span class="pill">Prototipo educativo</span></header>
<main>
<?php if($pagina==='inicio'): ?>
<section class="hero"><div><span class="eyebrow">TECNOLOGÍA Y SALUD</span><h1>Conoce tus síntomas.<br><em>Explora sus coincidencias.</em></h1><p>SEPE es una herramienta educativa que permite explorar asociaciones entre síntomas y enfermedades mediante un modelo de puntuación experimental.</p><a class="btn green" href="?pagina=sintomas">Iniciar evaluación →</a> <a class="btn secondary" href="?pagina=enfermedades">Ver catálogo</a></div><div class="illustration"><img src="assets/health.svg" alt="Ilustración de un expediente médico"></div></section>
<div class="stats"><article><strong><?= count($sintomas) ?></strong> Síntomas registrados</article><article><strong><?= $pdo->query('SELECT COUNT(*) FROM enfermedades')->fetchColumn() ?></strong> Enfermedades</article><article><strong>3</strong> Categorías de síntomas</article></div><h2>¿Cómo funciona?</h2><div class="grid steps"><article><b>01</b><h3>Selecciona síntomas</h3><p>Busca y elige los síntomas que deseas explorar.</p></article><article><b>02</b><h3>Analiza coincidencias</h3><p>El sistema compara los síntomas con asociaciones ponderadas.</p></article><article><b>03</b><h3>Consulta resultados</h3><p>Explora las puntuaciones y las referencias médicas.</p></article></div>
<?php elseif($pagina==='sintomas' && $_SERVER['REQUEST_METHOD']==='POST' && $seleccion): ?>
<div class="intro"><span class="eyebrow">PASO 02 / RESULTADOS</span><h1>Resultados del análisis</h1><p>Puntuaciones experimentales, no probabilidades médicas.</p></div>
<?php if($alerta): ?><div class="warning" role="alert"><strong>Atención: seleccionaste un síntoma de alarma.</strong><p>Si presentas dificultad para respirar o dolor u opresión en el pecho, busca atención médica urgente. No esperes el resultado de esta herramienta.</p></div><?php endif ?>
<div class="results"><section><?php foreach($resultados as $r): $pct=round(100*$r['coincidencia']/max(1,$r['maximo']),1); $d=$detalles[$r['id']]; ?><article class="result"><div class="between"><h3><?= h($r['nombre']) ?></h3><strong class="score"><?= h($pct) ?> / 100</strong></div><div class="bar"><div style="width:<?= h($pct) ?>%"></div></div><p><?= h($r['coincidencias']) ?> síntomas coincidentes</p><div class="tags"><?php foreach($d as $x): ?><span><?= h($x['nombre']) ?></span><?php endforeach ?></div><?php if($d): ?><a href="<?= h($d[0]['url']) ?>" target="_blank" rel="noopener noreferrer">Consultar fuente: <?= h($d[0]['institucion']) ?> ↗</a><?php endif ?></article><?php endforeach ?><?php if(!$resultados): ?><article>No se encontraron coincidencias.</article><?php endif ?></section><aside><article><h3>Síntomas seleccionados (<?= count($seleccion) ?>)</h3><div class="tags"><?php foreach($sintomas as $s): if(in_array((int)$s['id'],$seleccion,true)): ?><span><?= h($s['nombre']) ?></span><?php endif; endforeach ?></div><a class="btn green" href="?pagina=sintomas">Nueva evaluación</a><button class="btn secondary" onclick="window.print()">Imprimir / Guardar PDF</button></article></aside></div>
<?php elseif($pagina==='sintomas'): ?>
<div class="intro"><span class="eyebrow">PASO 01 / SÍNTOMAS</span><h1>¿Cómo te sientes hoy?</h1><p>Selecciona los síntomas que deseas analizar.</p></div><form method="post" action="?pagina=sintomas" id="evaluation"><input class="search" id="buscador" type="search" placeholder="Buscar entre <?= count($sintomas) ?> síntomas…" aria-label="Buscar síntomas"><p id="empty" hidden>No se encontraron síntomas.</p><div class="grid categories"><?php foreach(['General','Respiratorio','Digestivo y otros'] as $cat): ?><article><h3><?= h($cat) ?></h3><?php foreach($sintomas as $s): if($s['categoria']!==$cat) continue; ?><label class="check" data-name="<?= h($s['nombre']) ?>"><input type="checkbox" name="sintomas[]" value="<?= h($s['id']) ?>"><span><?= h($s['nombre']) ?></span></label><?php endforeach ?></article><?php endforeach ?></div><div class="warning" id="alarm" hidden role="alert">Si presentas dificultad para respirar o dolor u opresión en el pecho, busca atención médica urgente.</div><div class="actions"><strong><span id="contador">0</span> síntomas seleccionados</strong><div><button class="btn secondary" type="reset">Limpiar</button> <button class="btn green" type="submit" id="submit">Analizar síntomas →</button></div></div></form>
<?php elseif($pagina==='enfermedades'): ?><div class="intro"><span class="eyebrow">BIBLIOTECA</span><h1>Catálogo de enfermedades</h1><p>Consulta los síntomas asociados y sus pesos experimentales del 1 al 5.</p></div><input class="search" id="catalogSearch" type="search" placeholder="Buscar enfermedad…" aria-label="Buscar enfermedad"><div class="grid catalog"><?php foreach($catalogo as $nombre=>$items): ?><article data-name="<?= h($nombre) ?>"><h3><?= h($nombre) ?></h3><details><summary><?= count($items) ?> síntomas asociados</summary><?php foreach($items as $x): ?><div class="between symptom"><span><?= h($x['sintoma']) ?></span><b><?= h($x['peso']) ?>/5</b></div><?php endforeach ?></details></article><?php endforeach ?></div><p id="catalogEmpty" hidden>No se encontraron enfermedades.</p>
<?php else: ?><div class="intro"><span class="eyebrow">FUENTES DE INFORMACIÓN</span><h1>Referencias médicas</h1><p>Fuentes generales de asociaciones. Los pesos son experimentales y no proceden de estas instituciones.</p></div><div class="grid"><?php foreach($referencias as $r): ?><article><span class="eyebrow"><?= h($r['institucion']) ?></span><h3><?= h($r['titulo']) ?></h3><a href="<?= h($r['url']) ?>" target="_blank" rel="noopener noreferrer">Consultar referencia ↗</a></article><?php endforeach ?></div><?php endif ?>
<div class="notice"><strong>Información importante</strong><p>SEPE es un prototipo educativo y no sustituye una consulta médica. Las puntuaciones no representan probabilidades de padecer una enfermedad.</p></div></main><footer><strong>SEPE</strong> · Sistema de Predicción de Enfermedades <span>Proyecto educativo</span></footer></body></html>
