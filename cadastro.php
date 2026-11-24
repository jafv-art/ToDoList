<?php
require_once __DIR__ . '/conexao.php';

if (usuarioAtual()) {
    header('Location: index.php');
    exit;
}

$erro = '';
$nome = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    $confirmacao = $_POST['confirmacao'] ?? '';

    if (mb_strlen($nome) < 2) {
        $erro = 'Informe seu nome com pelo menos 2 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um endereço de e-mail válido.';
    } elseif (strlen($senha) < 8) {
        $erro = 'A senha precisa ter pelo menos 8 caracteres.';
    } elseif ($senha !== $confirmacao) {
        $erro = 'As senhas não coincidem.';
    } elseif (R::findOne('usuarios', 'email = ?', [$email])) {
        $erro = 'Já existe uma conta com esse e-mail.';
    } else {
        $conta = R::dispense('usuarios');
        $conta->nome = $nome;
        $conta->email = $email;
        $conta->senha = password_hash($senha, PASSWORD_DEFAULT);
        R::store($conta);

        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int) $conta->id;
        $_SESSION['usuario_nome'] = $nome;
        header('Location: index.php');
        exit;
    }
}

cabecalho('Criar conta');
?>
<section class="account-layout account-layout-register">
    <div class="account-story">
        <span class="section-label">ROTINA EM DIA / PRIMEIRO PASSO</span>
        <h1>Faça espaço para<br><em>o que importa.</em></h1>
        <p>Crie sua conta e monte uma rotina com suas próprias tarefas, categorias e prioridades.</p>
        <div class="story-stamp"><span>●</span><div><strong>SEU RITMO.</strong><small>SUA ORGANIZAÇÃO.</small></div></div>
    </div>
    <div class="account-card">
        <span class="section-label">COMECE POR AQUI</span>
        <h2>Criar minha conta</h2>
        <p class="account-hint">Seus dados ficam associados à sua lista pessoal.</p>
        <?php if ($erro): ?><div class="account-error"><?= e($erro) ?></div><?php endif; ?>
        <form method="post" class="account-form" autocomplete="on">
            <div class="field"><label for="nome">Nome</label><input id="nome" name="nome" maxlength="120" value="<?= e($nome) ?>" placeholder="Seu nome" autocomplete="name" required></div>
            <div class="field"><label for="email">E-mail</label><input id="email" name="email" type="email" maxlength="190" value="<?= e($email) ?>" placeholder="voce@email.com" autocomplete="email" required></div>
            <div class="field"><label for="senha">Senha</label><input id="senha" name="senha" type="password" minlength="8" placeholder="Pelo menos 8 caracteres" autocomplete="new-password" required></div>
            <div class="field"><label for="confirmacao">Confirmar senha</label><input id="confirmacao" name="confirmacao" type="password" minlength="8" placeholder="Digite novamente" autocomplete="new-password" required></div>
            <button class="account-submit" type="submit">Criar minha conta <span>→</span></button>
        </form>
        <p class="account-foot">Já possui cadastro? <a href="login.php">Entrar</a></p>
    </div>
</section>
<?php rodape(); ?>
