<?php
// registrar.php
require_once 'config/db.php';

$errores = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id  = intval($_POST['role_id'] ?? 0);

    // Validaciones
    if ($name === '' || $email === '' || $password === '' || $role_id === 0) {
        $errores[] = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido.";
    }

    if (empty($errores)) {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errores[] = "El correo ya está registrado.";
        } else {
            // Insertar usuario
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $hash, $role_id]);
            $success = "Registro exitoso. Ahora puedes iniciar sesión.";
            // Limpiar campos
            $name = $email = '';
            $role_id = 0;
        }
    }
}

// Obtener roles para el select
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-custom p-4">
            <h3 class="text-center mb-4">Registro de Usuario</h3>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="registrar.php">
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre completo</label>
                    <input 
                      type="text" 
                      name="name" 
                      id="name" 
                      class="form-control" 
                      value="<?php echo htmlspecialchars($name ?? ''); ?>"
                    >
                </div>

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

                <div class="mb-3">
                    <label for="role_id" class="form-label">Rol</label>
                    <select name="role_id" id="role_id" class="form-select">
                        <option value="0">-- Seleccione --</option>
                        <?php foreach ($roles as $rol): ?>
                            <option 
                              value="<?php echo $rol['id']; ?>"
                              <?php echo (isset($role_id) && $role_id == $rol['id']) ? 'selected' : ''; ?>
                            >
                                <?php echo $rol['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100">Registrarse</button>
                <p class="text-center mt-3">
                    ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
