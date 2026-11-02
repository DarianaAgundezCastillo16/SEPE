<?php

require __DIR__ . '/config.php';

function h($v)

{

    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

}

$sintomas = $pdo->query('SELECT id,nombre,categoria FROM sintomas ORDER BY FIELD(categoria,"General","Respiratorio","Digestivo y otros"),nombre')->fetchAll();

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

$pagina = $_GET['pagina'] ?? 'sintomas';

if (!in_array($pagina, ['sintomas', 'enfermedades', 'referencias'], true))

    $pagina = 'sintomas';

$enfermedades = $pagina === 'enfermedades' ? $pdo->query('SELECT e.nombre,s.nombre AS sintoma,es.peso FROM enfermedades e JOIN enfermedad_sintoma es ON e.id=es.enfermedad_id JOIN sintomas s ON s.id=es.sintoma_id ORDER BY e.nombre,es.peso DESC,s.nombre')->fetchAll() : [];

$referencias = $pagina === 'referencias' ? $pdo->query('SELECT * FROM referencias ORDER BY id')->fetchAll() : [];

?>

<!doctype html>

<html lang="es">



<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SEPE - Prototipo educativo</title>

    <style>

        body {

            font-family: Arial, sans-serif;

            font-size: 14px;

            max-width: 1050px;

            margin: 22px auto;

            padding: 0 18px;

            color: #111;

            background: white

        }



        header {

            display: flex;

            justify-content: space-between;

            align-items: flex-start

        }



        h1 {

            font-size: 23px;

            margin: 20px 0 10px

        }



        h2 {

            font-size: 17px;

            margin-top: 22px

        }



        small,

        .muted {

            color: #555

        }



        nav {

            margin: 22px 0

        }



        a.button,

        button {

            border: 1px solid #aaa;

            background: #f4f4f4;

            color: #111;

            text-decoration: none;

            padding: 8px 12px;

            font: inherit;

            cursor: pointer

        }



        a.button:hover,

        button:hover {

            background: #e9e9e9

        }



        .grid {

            display: grid;

            grid-template-columns: repeat(3, minmax(0, 1fr));

            gap: 24px

        }



        .check {

            display: block;

            margin: 9px 0;

            font-size: 13px

        }



        .check input {

            margin-right: 7px

        }



        .search {

            width: 100%;

            box-sizing: border-box;

            padding: 10px;

            border: 1px solid #bbb

        }



        .actions {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin: 25px 0

        }



        .bar {

            height: 11px;

            border: 1px solid #999;

            margin: 5px 0 17px

        }



        .fill {

            background: #222;

            height: 100%

        }



        .results {

            display: grid;

            grid-template-columns: 2fr 1fr;

            gap: 35px

        }



        .notice {

            border-top: 1px solid #ccc;

            padding-top: 14px;

            margin-top: 35px

        }



        .item {

            margin-bottom: 16px

        }



        .table {

            border-collapse: collapse;

            width: 100%

        }



        .table td,

        .table th {

            border-bottom: 1px solid #ddd;

            padding: 9px;

            text-align: left

        }



        label {

            cursor: pointer

        }



        @media(max-width:650px) {



            .grid,

            .results {

                grid-template-columns: 1fr

            }



            header {

                gap: 10px

            }



            .actions {

                gap: 12px

            }

        }

    </style>

</head>



<body>

    <header>

        <div><strong>✚ SEPE</strong><br><small>Sistema de Predicción de Enfermedades</small></div><small>Prototipo

            educativo</small>

    </header>

    <nav><a class="button" href="index.php">Evaluación de síntomas</a> <a class="button"

            href="?pagina=enfermedades">Enfermedades y síntomas</a> <a class="button"

            href="?pagina=referencias">Referencias</a></nav>

    <?php if ($pagina === 'sintomas'): ?>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $seleccion): ?>

            <small>① Síntomas &nbsp; ② Resultados</small>

            <h1>Resultados del análisis</h1>

            <p class="muted">Clasificación experimental según los síntomas seleccionados.</p>

            <div class="results">

                <section>

                    <h2>Puntuaciones del modelo por enfermedad</h2>

                    <?php foreach ($resultados as $r):

                        $pct = round(100 * (float) $r['coincidencia'] / max(1, (float) $r['maximo']), 1); ?>

                        <div class="item"><strong><?= h($r['nombre']) ?></strong> — <?= h($pct) ?> / 100 puntos

                            <small>(<?= h($r['coincidencias']) ?> coincidencias)</small>

                            <div class="bar">

                                <div class="fill" style="width:<?= h($pct) ?>%"></div>

                            </div>

                        </div>

                    <?php endforeach;

                    if (!$resultados): ?>

                        <p>No se encontraron coincidencias.</p><?php endif; ?>

                </section>

                <aside>

                    <h2>Síntomas seleccionados</h2><?php foreach ($sintomas as $s):

                        if (in_array((int) $s['id'], $seleccion, true)): ?>

                            <p><?= h($s['nombre']) ?></p><?php endif; endforeach; ?><small><?= count($seleccion) ?> síntomas

                        seleccionados</small>

                </aside>

            </div>

            <div class="notice"><strong>⚠ Aviso importante</strong>

                <p>Este resultado es experimental y no constituye un diagnóstico médico. Los puntos no indican la probabilidad

                    real de padecer una enfermedad. Si tienes dificultad para respirar, dolor persistente en el pecho o

                    confusión, busca atención médica urgente.</p>

            </div><a class="button" href="index.php">Volver a síntomas</a>

        <?php else: ?>

            <small>① Síntomas &nbsp; ② Resultados</small>

            <h1>Predicción de enfermedades</h1>

            <p>Selecciona los síntomas que presentas y se estimará una puntuación experimental de coincidencia.</p>

            <form method="post" action="index.php"><input id="buscador" class="search" type="search"

                    placeholder="Buscar entre <?= count($sintomas) ?> síntomas..." aria-label="Buscar síntomas">

                <div class="grid">

                    <?php foreach (['General', 'Respiratorio', 'Digestivo y otros'] as $categoria): ?>

                        <section>

                            <h2><?= h($categoria) ?></h2>

                            <?php foreach ($sintomas as $s):

                                if ($s['categoria'] !== $categoria)

                                    continue; ?><label class="check"

                                    data-name="<?= h(mb_strtolower($s['nombre'], 'UTF-8')) ?>"><input type="checkbox" name="sintomas[]"

                                        value="<?= h($s['id']) ?>"><?= h($s['nombre']) ?></label><?php endforeach; ?>

                        </section><?php endforeach; ?>

                </div>

                <div class="actions"><small><span id="contador">0</span> de <?= count($sintomas) ?> síntomas seleccionados</small>

                    <div><button type="reset">Limpiar</button> <button type="submit">Calcular coincidencias</button></div>

                </div>

            </form>

            <p class="muted">Los resultados son educativos y no equivalen a probabilidades médicas.</p>

            <script>const input = document.getElementById('buscador'), checks = [...document.querySelectorAll('input[type=checkbox]')], count = document.getElementById('contador'); input.addEventListener('input', () => { const q = input.value.trim().toLocaleLowerCase('es'); document.querySelectorAll('.check').forEach(el => el.hidden = !el.dataset.name.includes(q)) }); function update() { count.textContent = checks.filter(x => x.checked).length } checks.forEach(x => x.addEventListener('change', update)); document.querySelector('form').addEventListener('reset', () => { setTimeout(() => { input.value = ''; document.querySelectorAll('.check').forEach(el => el.hidden = false); update() }, 0) }); document.querySelector('form').addEventListener('submit', e => { if (!checks.some(x => x.checked)) { e.preventDefault(); alert('Selecciona al menos un síntoma.') } });</script>

        <?php endif; ?>

    <?php elseif ($pagina === 'enfermedades'): ?>

        <h1>Enfermedades y síntomas</h1>

        <p>Pesos experimentales del 1 al 5.</p>

        <?php $actual = '';

        foreach ($enfermedades as $r):

            if ($actual !== $r['nombre']) {

                if ($actual !== '')

                    echo '</ul>';

                $actual = $r['nombre'];

                echo '<h2>' . h($actual) . '</h2><ul>';

            }

            echo '<li>' . h($r['sintoma']) . ' — peso ' . h($r['peso']) . '</li>';

        endforeach;

        if ($actual !== '')

            echo '</ul>'; ?>

    <?php else: ?>

        <h1>Referencias médicas</h1>

        <p>Fuentes generales para seleccionar asociaciones entre enfermedades y síntomas. Los pesos fueron asignados con

            fines de demostración y no proceden de estas instituciones.</p>

        <table class="table">

            <tr>

                <th>Institución</th>

                <th>Referencia</th>

            </tr><?php foreach ($referencias as $r): ?>

                <tr>

                    <td><?= h($r['institucion']) ?></td>

                    <td><a href="<?= h($r['url']) ?>" target="_blank" rel="noopener noreferrer"><?= h($r['titulo']) ?></a></td>

                </tr><?php endforeach; ?>

        </table><?php endif; ?>

</body>



</html>