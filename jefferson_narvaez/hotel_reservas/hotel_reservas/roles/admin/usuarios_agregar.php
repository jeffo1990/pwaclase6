<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

if (!usuario_tiene_rol(['Administrator'])) {
    header('Location: /hotel_reservas/index.php');
    exit;
}

$errores = [];
$modo   = 'crear'; // por defecto: crear
$usuario = [
    'name'    => '',
    'email'   => '',
    'role_id' => 0
];

// Traer roles
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();

// Si viene edit_id, cargar datos para editar
if (isset($_GET['edit_id'])) {
    $modo = 'editar';
    $edit_id = intval($_GET['edit_id']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$edit_id]);
    $usuario = $stmt->fetch();
    if (!$usuario) {
        header('Location: usuarios.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role_id  = intval($_POST['role_id'] ?? 0);
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $role_id === 0) {
        $errores[] = "Nombre, correo y rol son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Correo inválido.";
    }

    if (empty($errores)) {
        if ($modo === 'editar') {
            // Actualizar usuario
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmtUpd = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, password = ?, role_id = ? 
                    WHERE id = ?
                ");
                $stmtUpd->execute([$name, $email, $hash, $role_id, $edit_id]);
            } else {
                $stmtUpd = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, role_id = ? 
                    WHERE id = ?
                ");
                $stmtUpd->execute([$name, $email, $role_id, $edit_id]);
            }
            header('Location: usuarios.php?msg=editado');
            exit;
        } else {
            // Crear nuevo usuario
            // Verificar si email existe
            $stmtChk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmtChk->execute([$email]);
            if ($stmtChk->rowCount() > 0) {
                $errores[] = "El correo ya está registrado.";
            } else {
                if ($password === '') {
                    $errores[] = "La contraseña es obligatoria para nuevos usuarios.";
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmtIns = $pdo->prepare("
                        INSERT INTO users (name, email, password, role_id)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmtIns->execute([$name, $email, $hash, $role_id]);
                    header('Location: usuarios.php?msg=agregado');
                    exit;
                }
            }
        }
    }
}

// Si modo editar, rellenar campos
if ($modo === 'editar' && isset($usuario)) {
    $name    = $usuario['name'];
    $email   = $usuario['email'];
    $role_id = $usuario['role_id'];
}

include '../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-custom p-4">
            <h4 class="mb-4">
                <?php echo ($modo === 'editar') ? "Editar Usuario" : "Agregar Usuario"; ?>
            </h4>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
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
                    <label for="role_id" class="form-label">Rol</label>
                    <select name="role_id" id="role_id" class="form-select">
                        <option value="0">-- Seleccione --</option>
                        <?php foreach ($roles as $rol): ?>
                            <option 
                              value="<?php echo $rol['id']; ?>"
                              <?php 
                                if (($modo === 'editar' && $rol['id'] == $role_id) || 
                                    ($modo === 'crear' && isset($role_id) && $role_id == $rol['id'])) {
                                    echo 'selected';
                                }
                              ?>
                            >
                              <?php echo $rol['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        Contraseña 
                        <?php echo ($modo === 'editar') ? "(dejar en blanco si no cambia)" : ""; ?>
                    </label>
                    <input 
                      type="password" 
                      name="password" 
                      id="password" 
                      class="form-control"
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <?php echo ($modo === 'editar') ? "Actualizar" : "Agregar"; ?>
                </button>
                <a 
                  href="/hotel_reservas/roles/admin/usuarios.php" 
                  class="btn btn-secondary w-100 mt-2"
                >
                  Cancelar
                </a>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
