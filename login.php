<?php
require_once __DIR__ . '/conexao.php';

if (usuarioAtual()) {
    header('Location: index.php');
    exit;
}

$erro = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
        $erro = 'Informe um e-mail válido e sua senha.';
    } else {
        $conta = R::findOne('usuarios', 'email = ?', [$email]);
        if ($conta && password_verify($senha, $conta->senha)) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int) $conta->id;
            $_SESSION['usuario_nome'] = (string) $conta->nome;
            header('Location: index.php');
            exit;
        }
        $erro = 'E-mail ou senha incorretos.';
    }
}

cabecalho('Entrar');
?>
<section class="account-layout">
    <div class="account-story">
        <span class="section-label">ROTINA EM DIA / SUA ÁREA</span>
        <h1>Cuide do seu tempo.<br><em>Uma tarefa por vez.</em></h1>
        <p>Entre para acessar sua rotina, acompanhar prioridades e manter suas tarefas organizadas em um espaço só seu.</p>
        <div class="story-stamp"><span>●</span><div><strong>MENOS PRESSA.</strong><small>MAIS INTENÇÃO.</small></div></div>
    </div>
    <div class="account-card">
        <span class="section-label">BOM TER VOCÊ DE VOLTA</span>
        <h2>Entrar na sua conta</h2>
        <p class="account-hint">Use o e-mail e a senha do seu cadastro.</p>
        <?php if ($erro): ?><div class="account-error"><?= e($erro) ?></div><?php endif; ?>
        <form method="post" class="account-form" autocomplete="on">
            <div class="field"><label for="email">E-mail</label><input id="email" name="email" type="email" maxlength="190" value="<?= e($email) ?>" placeholder="voce@email.com" autocomplete="email" required></div>
            <div class="field"><label for="senha">Senha</label><input id="senha" name="senha" type="password" placeholder="Sua senha" autocomplete="current-password" required></div>
            <button class="account-submit" type="submit">Acessar minha rotina <span>→</span></button>
        </form>
        <p class="account-foot">Ainda não tem cadastro? <a href="cadastro.php">Criar uma conta</a></p>
    </div>
</section>
<?php rodape(); ?>
