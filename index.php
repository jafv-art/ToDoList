<?php
require_once __DIR__ . '/conexao.php';
$usuario = exigirLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'alternar') {
    $tarefa = R::findOne('tarefas', 'id = ? AND usuario_id = ?', [
        (int) ($_POST['id'] ?? 0),
        $usuario['id']
    ]);

    if ($tarefa && $tarefa->id) {
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

<style>
.task-line {
    grid-template-columns: 32px 25px minmax(0, 1fr) 145px auto;
}

.task-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
}

.edit-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 9px 13px;
    border: 1px solid #cbd9d0;
    border-radius: 7px;
    background: #e5eee9;
    color: #2f6256;
    font: 700 11px Arial, sans-serif;
    transition: all .2s ease;
    white-space: nowrap;
}

.edit-button:hover {
    background: #2f6256;
    border-color: #2f6256;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(47, 98, 86, .18);
}

.edit-button svg {
    width: 14px;
    height: 14px;
}

.task-actions .delete {
    display: grid;
    width: 30px;
    height: 30px;
    place-items: center;
    border-radius: 7px;
    font-size: 21px;
    transition: .2s;
}

.task-actions .delete:hover {
    background: #f7e7e3;
}

@media (max-width: 700px) {
    .task-line {
        grid-template-columns: 24px 23px minmax(0, 1fr) auto;
        gap: 9px;
    }

    .task-actions {
        gap: 5px;
    }

    .edit-button {
        padding: 8px;
    }

    .edit-button span {
        display: none;
    }

    .edit-button svg {
        width: 17px;
        height: 17px;
    }
}
</style>

<section class="masthead">
    <div class="mast-copy">
        <span class="section-label">ROTINA EM DIA / 01</span>
        <h1>O que merece<br><em>sua atenção?</em></h1>
        <p>Uma lista direta para organizar as próximas ações.</p>
    </div>

    <div class="today-note">
        <span>STATUS DE HOJE</span>
        <strong><?= $abertas ?> tarefas abertas</strong>
        <small><?= $feitas ?> concluída(s) até agora</small>
    </div>
</section>

<?php if (isset($_GET['sucesso'])): ?>
    <div class="notice">Tarefa adicionada à sua lista.</div>
<?php endif; ?>

<?php if (isset($_GET['excluida'])): ?>
    <div class="notice">Tarefa excluída.</div>
<?php endif; ?>

<?php if (isset($_GET['editada'])): ?>
    <div class="notice">Tarefa atualizada com sucesso!</div>
<?php endif; ?>

<section class="quick-numbers">
    <div>
        <span>ABERTAS</span>
        <strong><?= $abertas ?></strong>
    </div>

    <div>
        <span>CONCLUÍDAS</span>
        <strong><?= $feitas ?></strong>
    </div>

    <div>
        <span>ALTA PRIORIDADE</span>
        <strong class="red-number"><?= $urgentes ?></strong>
    </div>
</section>

<section class="task-sheet">

    <header class="sheet-header">
        <div>
            <span class="section-label">LISTA DE TAREFAS</span>
            <h2><?= $filtro === 'todas' ? 'Todas as tarefas' : ucfirst($filtro) ?></h2>
        </div>

        <a class="add-button" href="inclusao.php">
            Adicionar tarefa <b>+</b>
        </a>
    </header>

    <nav class="filter-nav">
        <a class="<?= $filtro === 'todas' ? 'active' : '' ?>" href="?filtro=todas">
            Todas <small><?= $total ?></small>
        </a>

        <a class="<?= $filtro === 'pendentes' ? 'active' : '' ?>" href="?filtro=pendentes">
            A fazer <small><?= $abertas ?></small>
        </a>

        <a class="<?= $filtro === 'concluidas' ? 'active' : '' ?>" href="?filtro=concluidas">
            Concluídas <small><?= $feitas ?></small>
        </a>
    </nav>

    <?php if (!$tarefas): ?>

        <div class="empty">
            <strong>Esta lista está vazia.</strong>
            <span>Use o botão acima para organizar seu próximo passo.</span>
        </div>

    <?php else: ?>

        <div class="task-list">

            <?php $numero = 1; foreach ($tarefas as $tarefa): ?>

                <article class="task-line <?= $tarefa->concluida ? 'completed' : '' ?>">

                    <span class="task-number">
                        <?= str_pad((string) $numero++, 2, '0', STR_PAD_LEFT) ?>
                    </span>

                    <form method="post" class="check-form">
                        <input type="hidden" name="acao" value="alternar">
                        <input type="hidden" name="id" value="<?= (int) $tarefa->id ?>">

                        <button class="round-check" type="submit" title="Concluir tarefa">
                            <?= $tarefa->concluida ? '✓' : '' ?>
                        </button>
                    </form>

                    <div class="task-content">
                        <strong><?= e($tarefa->titulo) ?></strong>

                        <?php if ($tarefa->descricao): ?>
                            <span><?= e(mb_strimwidth($tarefa->descricao, 0, 94, '…')) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="task-side">
                        <span class="category"><?= e($tarefa->categoria) ?></span>

                        <span class="priority <?= strtolower((string) $tarefa->prioridade) ?>">
                            <?= e($tarefa->prioridade) ?>
                        </span>
                    </div>

                    <div class="task-actions">

                        <a
                            class="edit-button"
                            href="editar.php?id=<?= (int) $tarefa->id ?>"
                            title="Editar tarefa"
                            aria-label="Editar tarefa"
                        >
                            <svg viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"/>
                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L9 17l-4 1 1-4Z"/>
                            </svg>
                            <span>Editar</span>
                        </a>

                        <a
                            class="delete"
                            href="exclusao.php?id=<?= (int) $tarefa->id ?>"
                            title="Excluir tarefa"
                            aria-label="Excluir tarefa"
                        >×</a>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>

<?php rodape(); ?>
