<?php
require_once __DIR__ . '/conexao.php';
$usuario = exigirLogin();

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    if (mb_strlen($titulo) < 3) {
        $erro = 'Informe um título com pelo menos 3 caracteres.';
    } else {
        $tarefa = R::dispense('tarefas');
        $tarefa->titulo = $titulo;
        $tarefa->descricao = trim($_POST['descricao'] ?? '') ?: null;
        $tarefa->categoria = trim($_POST['categoria'] ?? 'Pessoal') ?: 'Pessoal';
        $tarefa->prioridade = in_array($_POST['prioridade'] ?? 'Normal', ['Baixa', 'Normal', 'Alta'], true) ? $_POST['prioridade'] : 'Normal';
        $tarefa->concluida = 0;
        $tarefa->usuario_id = $usuario['id'];
        R::store($tarefa);
        header('Location: index.php?sucesso=1');
        exit;
    }
}

cabecalho('Adicionar tarefa');
?>
<section class="form-page">
    <a class="back" href="index.php">← Voltar para a lista</a>
    <div class="form-heading"><span class="section-label">NOVA TAREFA / 02</span><h1>Qual é o próximo passo?</h1><p>Registre uma tarefa de forma rápida e objetiva.</p></div>
    <div class="form-card">
        <?php if ($erro): ?><div class="notice error"><?= e($erro) ?></div><?php endif; ?>
        <form method="post" action="inclusao.php" autocomplete="off">
            <div class="field full"><label for="titulo">Título</label><input id="titulo" name="titulo" maxlength="180" required autofocus placeholder="Ex.: revisar o trabalho final"></div>
            <div class="field full"><label for="descricao">Descrição <em>opcional</em></label><textarea id="descricao" name="descricao" placeholder="Algum detalhe importante?"></textarea></div>
            <div class="two-fields"><div class="field"><label for="categoria">Categoria</label><select id="categoria" name="categoria"><option>Pessoal</option><option>Trabalho</option><option>Estudos</option><option>Casa</option><option>Saúde</option></select></div><div class="field"><label for="prioridade">Prioridade</label><select id="prioridade" name="prioridade"><option>Normal</option><option>Alta</option><option>Baixa</option></select></div></div>
            <div class="actions"><a class="cancel" href="index.php">Cancelar</a><button class="save" type="submit">Adicionar tarefa</button></div>
        </form>
    </div>
</section>
<?php rodape(); ?>
