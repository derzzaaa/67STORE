<?php
/**
 * Страница входа — с защитой CSRF и Rate Limiting
 */

require_once 'includes/functions.php';

// Если уже авторизован, перенаправляем
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$blocked = false;
$retryAfter = 0;

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── 1. Проверка CSRF ───────────────────────────────────────────────────
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {

        // ── 2. Rate Limiting (5 попыток за 15 мин по IP) ──────────────────
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rlKey = 'login_' . $ip;
        $rl = checkRateLimit($rlKey, 5, 900);

        if (!$rl['allowed']) {
            $blocked = true;
            $retryAfter = ceil($rl['retry_after'] / 60);
            $error = "Too many login attempts. Try again in {$retryAfter} minute(s).";
        } else {

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Please enter email and password';
            } else {
                $result = loginUser($email, $password);

                if ($result['success']) {
                    resetRateLimit($rlKey); // Сбрасываем счётчик при успехе
                    // ── Безопасный редирект (только внутренние страницы) ──
                    $redirect = $_GET['redirect'] ?? 'index.php';
                    safeRedirect($redirect, 'index.php');
                } else {
                    $error = $result['message'];
                }
            }
        }
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>

<div class="container" style="max-width: 450px; margin: 80px auto;">
    <div style="background: white; padding: 40px; border-radius: 16px; box-shadow: var(--shadow-lg);">
        <h1 style="font-size: 32px; font-weight: 700; text-align: center; margin-bottom: 8px;">Welcome Back</h1>
        <p style="text-align: center; color: var(--color-gray); margin-bottom: 32px;">
            Login to your 7-SELECT account
        </p>

        <?php if ($error): ?>
            <div style="background: #FFE5E5; color: var(--color-primary); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!$blocked): ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Email</label>
                <input
                    type="email"
                    name="email"
                    value="<?php echo e($_POST['email'] ?? ''); ?>"
                    required
                    maxlength="255"
                    autocomplete="email"
                    autofocus
                    style="width: 100%; padding: 12px 16px; border: 1px solid #E5E5EA; border-radius: 8px; font-size: 16px;"
                >
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Password</label>
                <input
                    type="password"
                    name="password"
                    required
                    maxlength="128"
                    autocomplete="current-password"
                    style="width: 100%; padding: 12px 16px; border: 1px solid #E5E5EA; border-radius: 8px; font-size: 16px;"
                >
            </div>

            <button type="submit" class="btn-checkout" style="margin-bottom: 20px;">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>

            <p style="text-align: center; color: var(--color-gray); font-size: 14px;">
                Don't have an account?
                <a href="register.php" style="color: var(--color-primary); font-weight: 600;">Create one</a>
            </p>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
