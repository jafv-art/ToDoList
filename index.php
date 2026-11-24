<?php
require_once __DIR__ . '/conexao.php';
$usuario = exigirLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'alternar') {
    $tarefa = R::findOne('tarefas', 'id = ? AND usuario_id = ?', [(int) ($_POST['id'] ?? 0), $usuario['id']]);
    if ($tarefa->id) {
        $tarefa->concluida = $tarefa->concluida ? 0 : 1;
        R::store($tarefa);
    }
    header('Location: index.php');
    exit;
}

$filtro = $_GET['filtro'] ?? 'todas';
$ordem = 'ORDER BY concluida ASC, prioridade = "Alta" DESC, criada_em DESC';

if ($filtro === 'pendentes') {
    $tarefas = R::findAll('tarefas', 'WHERE usuario_id = ? AND concluida = 0 ' . $ordem, [$usuario['id']]);
} elseif ($filtro === 'concluidas') {
    $tarefas = R::findAll('tarefas', 'WHERE usuario_id = ? AND concluida = 1 ' . $ordem, [$usuario['id']]);
} else {
    $tarefas = R::findAll('tarefas', 'WHERE usuario_id = ? ' . $ordem, [$usuario['id']]);
}

$total = R::count('tarefas', 'usuario_id = ?', [$usuario['id']]);
$abertas = R::count('tarefas', 'usuario_id = ? AND concluida = 0', [$usuario['id']]);
$feitas = $total - $abertas;
$urgentes = R::count('tarefas', 'usuario_id = ? AND concluida = 0 AND prioridade = "Alta"', [$usuario['id']]);

cabecalho('Hoje');
?>
<section class="masthead">
    <div class="mast-copy">
        <span class="section-label">ROTINA EM DIA / 01</span>
        <h1>O que merece<br><em>sua atenção?</em></h1>
        <p>Uma lista direta para organizar as próximas ações.</p>
    </div>
    <div class="today-note"><span>STATUS DE HOJE</span><strong><?= $abertas ?> tarefas abertas</strong><small><?= $feitas ?> concluída(s) até agora</small></div>
</section>

<?php if (isset($_GET['sucesso'])): ?><div class="notice">Tarefa adicionada à sua lista.</div><?php endif; ?>
<?php if (isset($_GET['excluida'])): ?><div class="notice">Tarefa excluída.</div><?php endif; ?>

<section class="quick-numbers">
    <div><span>ABERTAS</span><strong><?= $abertas ?></strong></div>
    <div><span>CONCLUÍDAS</span><strong><?= $feitas ?></strong></div>
    <div><span>ALTA PRIORIDADE</span><strong class="red-number"><?= $urgentes ?></strong></div>
</section>

<section class="task-sheet">
    <header class="sheet-header">
        <div><span class="section-label">LISTA DE TAREFAS</span><h2><?= $filtro === 'todas' ? 'Todas as tarefas' : ucfirst($filtro) ?></h2></div>
        <a class="add-button" href="inclusao.php">Adicionar tarefa <b>+</b></a>
    </header>
    <nav class="filter-nav">
        <a class="<?= $filtro === 'todas' ? 'active' : '' ?>" href="?filtro=todas">Todas <small><?= $total ?></small></a>
        <a class="<?= $filtro === 'pendentes' ? 'active' : '' ?>" href="?filtro=pendentes">A fazer <small><?= $abertas ?></small></a>
        <a class="<?= $filtro === 'concluidas' ? 'active' : '' ?>" href="?filtro=concluidas">Concluídas <small><?= $feitas ?></small></a>
    </nav>

    <?php if (!$tarefas): ?>
        <div class="empty"><strong>Esta lista está vazia.</strong><span>Use o botão acima para organizar seu próximo passo.</span></div>
    <?php else: ?>
        <div class="task-list">
            <?php $numero = 1; foreach ($tarefas as $tarefa): ?>
                <article class="task-line <?= $tarefa->concluida ? 'completed' : '' ?>">
                    <span class="task-number"><?= str_pad((string) $numero++, 2, '0', STR_PAD_LEFT) ?></span>
                    <form method="post" class="check-form">
                        <input type="hidden" name="acao" value="alternar">
                        <input type="hidden" name="id" value="<?= (int) $tarefa->id ?>">
                        <button class="round-check" type="submit" title="Concluir tarefa"><?= $tarefa->concluida ? '✓' : '' ?></button>
                    </form>
                    <div class="task-content"><strong><?= e($tarefa->titulo) ?></strong><?php if ($tarefa->descricao): ?><span><?= e(mb_strimwidth($tarefa->descricao, 0, 94, '…')) ?></span><?php endif; ?></div>
                    <div class="task-side"><span class="category"><?= e($tarefa->categoria) ?></span><span class="priority <?= strtolower((string) $tarefa->prioridade) ?>"><?= e($tarefa->prioridade) ?></span></div>
                    <a class="delete" href="exclusao.php?id=<?= (int) $tarefa->id ?>" title="Excluir tarefa">×</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php rodape(); ?>
