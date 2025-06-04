<?php
// login.php
require_once 'config/db.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errores[] = "Todos los campos son obligatorios.";
    } else {
        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.password, r.name AS role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Autenticación exitosa
            session_start();
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role_name'] = $user['role_name'];

            // Redirigir siempre al dashboard general
            header('Location: /hotel_reservas/dashboard.php');
            exit;
        } else {
            $errores[] = "Credenciales incorrectas.";
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-custom p-4">
            <h3 class="text-center mb-4">Iniciar Sesión</h3>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input
                      type="email"
                      name="email"
                      id="email"
                      class="form-control"
                      value="<?php echo htmlspecialchars($email ?? ''); ?>"
                    >
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input
                      type="password"
                      name="password"
                      id="password"
                      class="form-control"
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                <p class="text-center mt-3">
                    ¿No tienes cuenta? <a href="registrar.php">Regístrate aquí</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
