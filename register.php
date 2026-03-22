<?php
/**
 * Страница регистрации — с защитой CSRF и усиленной валидацией
 */

require_once 'includes/functions.php';

// Если уже авторизован, перенаправляем
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── 1. Проверка CSRF ───────────────────────────────────────────────────
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {

        $email           = trim($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $firstName       = trim($_POST['first_name'] ?? '');
        $lastName        = trim($_POST['last_name'] ?? '');
        $phone           = trim($_POST['phone'] ?? '');

        // ── 2. Валидация полей ─────────────────────────────────────────────
        if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            $error = 'Please fill in all required fields';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
            $error = 'Please enter a valid email address';
        } elseif (!preg_match('/^[\p{L}\s\-\']{1,100}$/u', $firstName)) {
            $error = 'First name must contain only letters (max 100 characters)';
        } elseif (!preg_match('/^[\p{L}\s\-\']{1,100}$/u', $lastName)) {
            $error = 'Last name must contain only letters (max 100 characters)';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters';
        } elseif (!preg_match('/\d/', $password)) {
            $error = 'Password must contain at least one number';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (!empty($phone) && !preg_match('/^[\+\d\s\-\(\)]{7,20}$/', $phone)) {
            $error = 'Please enter a valid phone number';
        } else {
            $result = registerUser($email, $password, $firstName, $lastName, $phone ?: null);

            if ($result['success']) {
                safeRedirect('index.php');
            } else {
                $error = $result['message'];
            }
        }
    }
}

$pageTitle = 'Register';
require_once 'includes/header.php';
?>

<div class="container" style="max-width: 500px; margin: 60px auto;">
    <div style="background: white; padding: 40px; border-radius: 16px; box-shadow: var(--shadow-lg);">
        <h1 style="font-size: 32px; font-weight: 700; text-align: center; margin-bottom: 8px;">Create Account</h1>
        <p style="text-align: center; color: var(--color-gray); margin-bottom: 32px;">
            Join 7-SELECT and get 10% off your first order!
        </p>

        <?php if ($error): ?>
            <div style="background: #FFE5E5; color: var(--color-primary); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">
                    First Name <span style="color: var(--color-primary);">*</span>
                </label>
                <input
                    type="text"
                    name="first_name"
                    value="<?php echo e($_POST['first_name'] ?? ''); ?>"
                    required
                    maxlength="100"
                    autocomplete="given-name"
                    style="width: 100%; padding: 12px 16px; border: 1px solid #E5E5EA; border-radius: 8px; font-size: 16px;"
                >
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">
                    Last Name <span style="color: var(--color-primary);">*</span>
                </label>
                <input
                    type="text"
                    name="last_name"
                    value="<?php echo e($_POST['last_name'] ?? ''); ?>"
                    required
                    maxlength="100"
                    autocomplete="family-name"
                    style="width: 100%; padding: 12px 16px; border: 1px solid #E5E5EA; border-radius: 8px; font-size: 16px;"
                >
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">
                    Email <span style="color: var(--color-primary);">*</span>
                </label>
                <input
                    type="email"
                    name="email"
                    value="<?php echo e($_POST['email'] ?? ''); ?>"
                    required
                    maxlength="255"
                    autocomplete="email"
                    style="width: 100%; padding: 12px 16px; border: 1px solid #E5E5EA; border-radius: 8px; font-size: 16px;"
                >
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">
                    Phone (optional)
                </label>
                <input
                    type="tel"
                    name="phone"
                    value="<?php echo e($_POST['phone'] ?? ''); ?>"
                    maxlength="20"
                    autocomplete="tel"
                    placeholder="+7 (999) 123-4567"
                    style="width: 100%; padding: 12px 16px; border: 1px solid #E5E5EA; border-radius: 8px; font-size: 16px;"
                >
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">
                    Password <span style="color: var(--color-primary);">*</span>
                </label>
                <input
                    type="password"
                    name="password"
                    required
                    minlength="8"
                    maxlength="128"
                    autocomplete="new-password"
                    style="width: 100%; padding: 12px 16px; border: 1px solid #E5E5EA; border-radius: 8px; font-size: 16px;"
                >
                <small style="color: var(--color-gray); font-size: 12px;">Minimum 8 characters, at least one number</small>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">
                    Confirm Password <span style="color: var(--color-primary);">*</span>
                </label>
                <input
                    type="password"
                    name="confirm_password"
                    required
                    maxlength="128"
                    autocomplete="new-password"
                    style="width: 100%; padding: 12px 16px; border: 1px solid #E5E5EA; border-radius: 8px; font-size: 16px;"
                >
            </div>

            <button type="submit" class="btn-checkout" style="margin-bottom: 20px;">
                <i class="fas fa-user-plus"></i> Create Account
            </button>

            <p style="text-align: center; color: var(--color-gray); font-size: 14px;">
                Already have an account?
                <a href="login.php" style="color: var(--color-primary); font-weight: 600;">Login</a>
            </p>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
