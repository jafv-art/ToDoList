<?php
require_once __DIR__ . '/conexao.php';
$usuario = exigirLogin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$tarefa = R::findOne('tarefas', 'id = ? AND usuario_id = ?', [$id, $usuario['id']]);
if (!$tarefa->id) {
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    R::trash($tarefa);
    header('Location: index.php?excluida=1');
    exit;
}

cabecalho('Excluir tarefa');
?>
<section class="confirm-page">
    <a class="back" href="index.php">← Voltar para a lista</a>
    <div class="confirm-card">
        <div class="warning-icon">!</div>
        <span class="section-label">REMOVER TAREFA / 03</span>
        <h1>Remover esta tarefa?</h1>
        <p>Ela será excluída da sua lista permanentemente.</p>
        <div class="task-preview"><strong><?= e($tarefa->titulo) ?></strong><span><?= e($tarefa->categoria) ?> · <?= e($tarefa->prioridade) ?></span></div>
        <form method="post" class="actions"><input type="hidden" name="id" value="<?= (int) $tarefa->id ?>"><a class="cancel" href="index.php">Cancelar</a><button class="delete-button" type="submit">Excluir tarefa</button></form>
    </div>
</section>
<?php rodape(); ?>
