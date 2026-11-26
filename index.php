<?php

require __DIR__ . '/config.php';

function h($v)

{

    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

}

$sintomas = $pdo->query('SELECT id,nombre,categoria FROM sintomas ORDER BY CASE categoria WHEN \'General\' THEN 0 WHEN \'Respiratorio\' THEN 1 ELSE 2 END,categoria,nombre')->fetchAll();

$validos = array_map('intval', array_column($sintomas, 'id'));

$seleccion = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['sintomas'] ?? [])), fn($id) => in_array($id, $validos, true))));

$resultados = [];
$edad = filter_var($_POST['edad'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0,'max_range'=>120]]);
$sexo = is_string($_POST['sexo'] ?? null) ? $_POST['sexo'] : '';
$rangos = [[0,1,'Bebés'],[2,5,'Primera infancia'],[6,12,'Niñez'],[13,17,'Adolescencia'],[18,39,'Adultos jóvenes'],[40,64,'Adultos'],[65,79,'Adultos mayores'],[80,120,'Edad avanzada']];
$grupoEdad = '';
foreach($rangos as [$min,$max,$nombre]) if($edad !== false && $edad >= $min && $edad <= $max) $grupoEdad = "{$nombre} ({$min}–{$max})";
$categorias = array_values(array_unique(array_column($sintomas,'categoria')));
$alarmas = ['Dificultad para respirar','Dolor torácico','Opresión en el pecho','Confusión repentina','Hundimiento de costillas al respirar','Pausas respiratorias','Estridor'];
$perfilValido = $edad !== false && in_array($sexo,['Femenino','Masculino'],true);
$evaluado = $_SERVER['REQUEST_METHOD'] === 'POST' && $perfilValido && count($seleccion)>0;
$error = $_SERVER['REQUEST_METHOD'] === 'POST' && !$evaluado;

if ($evaluado) {

    $placeholders = implode(',', array_fill(0, count($seleccion), '?'));

    // Puntaje: suma de pesos coincidentes / suma de pesos de todos los síntomas registrados para esa enfermedad.

    // También se exige coincidencia de al menos un síntoma. No representa probabilidad médica.

    $sql = "SELECT * FROM (SELECT e.id,e.nombre, SUM(CASE WHEN es.sintoma_id IN ($placeholders) THEN es.peso ELSE 0 END) AS coincidencia, SUM(es.peso) AS maximo, SUM(CASE WHEN es.sintoma_id IN ($placeholders) THEN 1 ELSE 0 END) AS coincidencias FROM enfermedades e JOIN enfermedad_sintoma es ON es.enfermedad_id=e.id GROUP BY e.id,e.nombre) AS puntuaciones WHERE coincidencia > 0 ORDER BY (coincidencia/maximo) DESC, coincidencia DESC, nombre ASC";

    $stmt = $pdo->prepare($sql);

    $stmt->execute(array_merge($seleccion, $seleccion));

    $resultados = $stmt->fetchAll();
    $qe=$pdo->prepare('SELECT factor,fuente,edad_minima,edad_maxima FROM riesgo_edad WHERE enfermedad_id=? AND ? BETWEEN edad_minima AND edad_maxima ORDER BY factor DESC,id LIMIT 1');
    $qs=$pdo->prepare('SELECT factor,fuente FROM riesgo_sexo WHERE enfermedad_id=? AND sexo=? LIMIT 1');
    foreach($resultados as &$r){
        $qe->execute([$r['id'],$edad]); $a=$qe->fetch();
        $qs->execute([$r['id'],$sexo]); $b=$qs->fetch();
        $r['base']=100*$r['coincidencia']/max(1,$r['maximo']);
        $r['fe']=(float)($a['factor']??1); $r['fs']=(float)($b['factor']??1);
         $r['reglaEdad'] = (bool)$a;
        $r['fuentes']=array_unique(array_filter([$a['fuente']??null,$b['fuente']??null], fn($url)=>is_string($url) && preg_match('~^https://~i',$url)));
        $r['ajusteEdad']=$r['base']*($r['fe']-1);
        $r['final']=$r['base']*$r['fe']*$r['fs'];
    } unset($r);
    usort($resultados,fn($a,$b)=>($b['final']<=>$a['final']) ?: ($b['coincidencia']<=>$a['coincidencia']) ?: strcmp($a['nombre'],$b['nombre']));
    $resultados=array_slice($resultados,0,5);

}

$pagina = $_GET['pagina'] ?? 'inicio';

if (!in_array($pagina, ['inicio', 'sintomas', 'enfermedades', 'referencias'], true))

    $pagina = 'sintomas';

$enfermedades = $pagina === 'enfermedades' ? $pdo->query('SELECT e.nombre,s.nombre AS sintoma,es.peso FROM enfermedades e JOIN enfermedad_sintoma es ON e.id=es.enfermedad_id JOIN sintomas s ON s.id=es.sintoma_id ORDER BY e.nombre,es.peso DESC,s.nombre')->fetchAll() : [];

$referencias = $pagina === 'referencias' ? $pdo->query('SELECT * FROM referencias ORDER BY id')->fetchAll() : [];

$catalogo = []; foreach ($enfermedades as $e) $catalogo[$e['nombre']][] = $e;
$alerta = array_filter($sintomas, fn($s)=>in_array((int)$s['id'],$seleccion,true) && in_array($s['nombre'],$alarmas,true));
$detalles = []; if ($resultados) { $q = $pdo->prepare('SELECT s.nombre, r.url, r.institucion FROM enfermedad_sintoma es JOIN sintomas s ON s.id=es.sintoma_id JOIN enfermedades e ON e.id=es.enfermedad_id JOIN referencias r ON r.id=e.referencia_id WHERE e.id=? AND s.id IN ('.implode(',',array_fill(0,count($seleccion),'?')).')'); foreach($resultados as $r){$q->execute(array_merge([$r['id']],$seleccion)); $detalles[$r['id']]=$q->fetchAll();} }
?>

<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SEPE | Evaluación de síntomas</title><link rel="stylesheet" href="assets/style.css"><script defer src="assets/app.js"></script></head><body>
<header><a class="brand" href="index.php"><span class="logo">✚</span><span>SE<span>PE</span></span></a><nav><?php foreach(['inicio'=>'Inicio','sintomas'=>'Evaluación','enfermedades'=>'Enfermedades','referencias'=>'Referencias'] as $k=>$v): ?><a class="<?= $pagina===$k?'active':'' ?>" href="?pagina=<?= $k ?>"><?= $v ?></a><?php endforeach ?></nav><span class="pill">Prototipo educativo</span></header>
<main>
<?php if($pagina==='inicio'): ?>
<section class="hero"><div><span class="eyebrow">TECNOLOGÍA Y SALUD</span><h1>Conoce tus síntomas.<br><em>Explora sus coincidencias.</em></h1><p>SEPE es una herramienta educativa que permite explorar asociaciones entre síntomas y enfermedades mediante un modelo de puntuación experimental.</p><a class="btn green" href="?pagina=sintomas">Iniciar evaluación →</a> <a class="btn secondary" href="?pagina=enfermedades">Ver catálogo</a></div><div class="illustration"><img src="assets/health.svg" alt="Ilustración de un expediente médico"></div></section>
<div class="stats"><article><strong><?= count($sintomas) ?></strong> Síntomas registrados</article><article><strong><?= $pdo->query('SELECT COUNT(*) FROM enfermedades')->fetchColumn() ?></strong> Enfermedades</article><article><strong><?= count($categorias) ?></strong> Categorías de síntomas</article></div><h2>¿Cómo funciona?</h2><div class="grid steps"><article><b>01</b><h3>Completa tu perfil</h3><p>Ingresa edad y sexo, luego selecciona tus síntomas.</p></article><article><b>02</b><h3>Analiza coincidencias</h3><p>El sistema compara los síntomas con asociaciones ponderadas.</p></article><article><b>03</b><h3>Consulta resultados</h3><p>Explora las puntuaciones y las referencias médicas.</p></article></div>
<?php elseif($pagina==='sintomas' && $evaluado): ?>
<div class="intro"><span class="eyebrow">PASO 03 / RESULTADOS</span><h1>Resultados del análisis</h1><p>Puntuaciones experimentales ajustadas por edad y sexo, no probabilidades médicas. Los multiplicadores son ilustrativos y no están validados clínicamente.</p></div>
<?php if($alerta): ?><div class="warning" role="alert"><strong>Atención: seleccionaste un síntoma de alarma.</strong><p>Los síntomas de alarma seleccionados pueden requerir atención inmediata. Busca atención médica urgente; si hay dificultad respiratoria grave, pausas respiratorias, dolor torácico intenso o confusión repentina, llama a emergencias. No esperes el resultado de esta herramienta.</p></div><?php endif ?>
<div class="results"><section><?php foreach($resultados as $r): $pct=round($r['final'],1); $d=$detalles[$r['id']]; ?><article class="result"><div class="between"><h3><?= h($r['nombre']) ?></h3><strong class="score"><?= h($pct) ?> pts</strong></div><div class="factorbox"><div>Base por síntomas: <b><?= h(round($r['base'],1)) ?> / 100</b></div><div>Grupo de edad: <b><?= h($grupoEdad) ?></b></div><div>Factor por edad: ×<?= h(number_format($r['fe'],2)) ?> · Sexo: ×<?= h(number_format($r['fs'],2)) ?></div><div>Ajuste por edad: <b><?= h(sprintf('%+.1f',$r['ajusteEdad'])) ?> pts</b> (antes del factor sexo).</div><div><?= !$r['reglaEdad'] ? 'No hay regla de edad registrada; se usa 1.00. Importa actualizar_bd.sql.' : ($r['fe']>1 ? 'La regla experimental de edad aumenta la puntuación.' : ($r['fe']<1 ? 'La regla experimental de edad disminuye la puntuación.' : 'La regla experimental de edad mantiene la puntuación.')) ?></div><div>Resultado = base × edad × sexo (puede superar 100 puntos).</div><?php foreach($r['fuentes'] as $url): ?><a href="<?= h($url) ?>" target="_blank" rel="noopener noreferrer">Contexto médico del factor ↗</a><br><?php endforeach ?></div><p><?= h($r['coincidencias']) ?> síntomas coincidentes</p><div class="tags"><?php foreach($d as $x): ?><span><?= h($x['nombre']) ?></span><?php endforeach ?></div><?php if($d): ?><a href="<?= h($d[0]['url']) ?>" target="_blank" rel="noopener noreferrer">Consulta general: <?= h($d[0]['institucion']) ?> ↗</a><?php endif ?></article><?php endforeach ?><?php if(!$resultados): ?><article>No se encontraron coincidencias.</article><?php endif ?></section><aside><article><h3>Perfil del paciente</h3><p><?= h($edad) ?> años · <?= h($sexo) ?></p><p><?= h($grupoEdad) ?></p><h3>Síntomas seleccionados (<?= count($seleccion) ?>)</h3><div class="tags"><?php foreach($sintomas as $s): if(in_array((int)$s['id'],$seleccion,true)): ?><span><?= h($s['nombre']) ?></span><?php endif; endforeach ?></div><a class="btn green" href="?pagina=sintomas">Nueva evaluación</a><button class="btn secondary" onclick="window.print()">Imprimir / Guardar PDF</button></article></aside></div>
<?php elseif($pagina==='sintomas'): ?>
<div class="intro"><span class="eyebrow">PASOS 01 Y 02 / PERFIL Y SÍNTOMAS</span><h1>¿Cómo te sientes hoy?</h1><p>Selecciona los síntomas que deseas analizar.</p></div><form method="post" action="?pagina=sintomas" id="evaluation"><?php if($error): ?><div class="warning" role="alert">Ingresa una edad válida, selecciona el sexo y al menos un síntoma.</div><?php endif ?><article class="profile"><span class="eyebrow">PASO 01</span><h2>Perfil del paciente</h2><p>Datos obligatorios. No se guardan en la base de datos.</p><div class="profilegrid"><label>Edad en años *<input name="edad" id="edad" type="number" min="0" max="120" step="1" required value="<?= $edad!==false?h($edad):'' ?>" placeholder="Ej. 20"></label><label>Sexo biológico *<select name="sexo" id="sexo" required><option value="">Selecciona</option><?php foreach(['Femenino','Masculino'] as $op): ?><option <?= $sexo===$op?'selected':'' ?>><?= h($op) ?></option><?php endforeach ?></select></label></div><p>Los factores son experimentales y no representan probabilidades clínicas.</p><button type="button" class="btn green" id="nextStep">Continuar a síntomas →</button></article><section id="symptomStep" <?= $error ? '' : 'hidden' ?>><span class="eyebrow">PASO 02</span><h2>Selecciona tus síntomas</h2><input class="search" id="buscador" type="search" placeholder="Buscar entre <?= count($sintomas) ?> síntomas…" aria-label="Buscar síntomas"><label class="categoryfilter">Categoría <select id="categoryFilter"><option value="">Todas</option><?php foreach($categorias as $c): ?><option><?= h($c) ?></option><?php endforeach ?></select></label><p id="ageHint" aria-live="polite"></p><p id="empty" hidden>No se encontraron síntomas.</p><p class="symptom-help">Abre una categoría para ver sus síntomas. Puedes seleccionar varios y cambiar de categoría sin perderlos.</p><div id="selectedSymptoms" class="selected-symptoms" hidden></div><div class="grid categories"><?php foreach($categorias as $cat): ?><article data-category="<?= h($cat) ?>"><details><summary><span><?= h($cat) ?></span><small class="category-count"><?= count(array_filter($sintomas, fn($s)=>$s['categoria']===$cat)) ?> síntomas</small></summary><div class="category-body"><?php foreach($sintomas as $s): if($s['categoria']!==$cat) continue; ?><label class="check" data-name="<?= h($s['nombre']) ?>" data-alarm="<?= in_array($s['nombre'],$alarmas,true)?'1':'0' ?>"><input type="checkbox" name="sintomas[]" value="<?= h($s['id']) ?>" <?= in_array((int)$s['id'],$seleccion,true)?'checked':'' ?>><span><?= h($s['nombre']) ?></span></label><?php endforeach ?></div></details></article><?php endforeach ?></div><div class="warning" id="alarm" hidden role="alert">Los síntomas de alarma seleccionados pueden requerir atención inmediata. Busca atención médica urgente; si hay dificultad respiratoria grave, pausas respiratorias, dolor torácico intenso o confusión repentina, llama a emergencias.</div><div class="actions"><strong><span id="contador">0</span> síntomas seleccionados</strong><div><button class="btn secondary" type="reset">Limpiar</button> <button class="btn green" type="submit" id="submit">Analizar síntomas →</button></div></div></section></form>
<?php elseif($pagina==='enfermedades'): ?><div class="intro"><span class="eyebrow">BIBLIOTECA</span><h1>Catálogo de enfermedades</h1><p>Consulta los síntomas asociados y sus pesos experimentales del 1 al 5.</p></div><input class="search" id="catalogSearch" type="search" placeholder="Buscar enfermedad…" aria-label="Buscar enfermedad"><div class="grid catalog"><?php foreach($catalogo as $nombre=>$items): ?><article data-name="<?= h($nombre) ?>"><h3><?= h($nombre) ?></h3><details><summary><?= count($items) ?> síntomas asociados</summary><?php foreach($items as $x): ?><div class="between symptom"><span><?= h($x['sintoma']) ?></span><b><?= h($x['peso']) ?>/5</b></div><?php endforeach ?></details></article><?php endforeach ?></div><p id="catalogEmpty" hidden>No se encontraron enfermedades.</p>
<?php else: ?><div class="intro"><span class="eyebrow">FUENTES DE INFORMACIÓN</span><h1>Referencias médicas</h1><p>Fuentes de consulta general; las asociaciones nuevas no han sido verificadas individualmente. Los pesos son experimentales y no proceden de estas instituciones.</p></div><div class="grid"><?php foreach($referencias as $r): ?><article><span class="eyebrow"><?= h($r['institucion']) ?></span><h3><?= h($r['titulo']) ?></h3><a href="<?= h($r['url']) ?>" target="_blank" rel="noopener noreferrer">Consultar referencia ↗</a></article><?php endforeach ?></div><?php endif ?>
<div class="notice"><strong>Información importante</strong><p>SEPE es un prototipo educativo y no sustituye una consulta médica. Las puntuaciones no representan probabilidades de padecer una enfermedad.</p></div></main><footer><strong>SEPE</strong> · Sistema de Predicción de Enfermedades <span>Proyecto educativo</span></footer></body></html>
