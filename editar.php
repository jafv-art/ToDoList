<?php
require_once __DIR__ . '/conexao.php';
$usuario = exigirLogin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$tarefa = R::findOne(
    'tarefas',
    'id = ? AND usuario_id = ?',
    [$id, $usuario['id']]
);

if (!$tarefa || !$tarefa->id) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = trim($_POST['categoria'] ?? 'Pessoal');
    $prioridade = $_POST['prioridade'] ?? 'Normal';
    $prazo = trim($_POST['prazo'] ?? '');

    if (mb_strlen($titulo) < 3) {
        $erro = 'O título precisa ter pelo menos 3 caracteres.';
    } elseif (!in_array($prioridade, ['Baixa', 'Normal', 'Alta'], true)) {
        $erro = 'Prioridade inválida.';
    } elseif ($prazo !== '' && (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $prazo) ||
        !checkdate(
            (int) substr($prazo, 5, 2),
            (int) substr($prazo, 8, 2),
            (int) substr($prazo, 0, 4)
        )
    )) {
        $erro = 'Informe uma data válida.';
    } else {

        $tarefa->titulo = $titulo;
        $tarefa->descricao = $descricao ?: null;
        $tarefa->categoria = $categoria ?: 'Pessoal';
        $tarefa->prioridade = $prioridade;
        $tarefa->prazo = $prazo ?: null;

        R::store($tarefa);

        header('Location: index.php?editada=1');
        exit;
    }
}

cabecalho('Editar tarefa');
?>

<section class="form-page">

    <a class="back" href="index.php">
        ← Voltar para a lista
    </a>

    <div class="form-heading">
        <span class="section-label">EDITAR TAREFA / 02</span>
        <h1>Vamos ajustar seus planos?</h1>
        <p>Atualize as informações do seu objetivo.</p>
    </div>

    <div class="form-card">

        <?php if ($erro): ?>
            <div class="notice error">
                <?= e($erro) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="editar.php" autocomplete="off">

            <input type="hidden" name="id" value="<?= (int) $tarefa->id ?>">

            <div class="field full">
                <label for="titulo">Título</label>

                <input
                    id="titulo"
                    name="titulo"
                    maxlength="180"
                    required
                    value="<?= e($tarefa->titulo) ?>"
                >
            </div>

            <div class="field full">
                <label for="descricao">
                    Descrição <em>opcional</em>
                </label>

                <textarea
                    id="descricao"
                    name="descricao"
                    placeholder="Algum detalhe importante?"
                ><?= e($tarefa->descricao) ?></textarea>
            </div>

            <div class="two-fields">

                <div class="field">
                    <label for="categoria">Categoria</label>

                    <select id="categoria" name="categoria">
                        <?php
                        $categorias = [
                            'Pessoal',
                            'Trabalho',
                            'Estudos',
                            'Casa',
                            'Saúde'
                        ];

                        foreach ($categorias as $opcao):
                        ?>
                            <option
                                value="<?= e($opcao) ?>"
                                <?= $tarefa->categoria === $opcao ? 'selected' : '' ?>
                            >
                                <?= e($opcao) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="prioridade">Prioridade</label>

                    <select id="prioridade" name="prioridade">
                        <?php foreach (['Normal', 'Alta', 'Baixa'] as $opcao): ?>
                            <option
                                value="<?= e($opcao) ?>"
                                <?= $tarefa->prioridade === $opcao ? 'selected' : '' ?>
                            >
                                <?= e($opcao) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="field">
                <label for="prazo">Prazo de conclusão</label>

                <input
                    type="date"
                    id="prazo"
                    name="prazo"
                    value="<?= e($tarefa->prazo ? (string) $tarefa->prazo : '') ?>"
                >
            </div>

            <div class="actions">

                <a class="cancel" href="index.php">
                    Cancelar
                </a>

                <button class="save" type="submit">
                    ✓ Salvar alterações
                </button>

            </div>

        </form>

    </div>

</section>

<?php rodape(); ?>
