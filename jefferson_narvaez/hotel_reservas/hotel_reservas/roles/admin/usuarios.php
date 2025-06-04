<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

if (!usuario_tiene_rol(['Administrator'])) {
    header('Location: /hotel_reservas/index.php');
    exit;
}

// Eliminar usuario
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    // Evitar que el administrador se borre a sí mismo
    if ($id !== intval($_SESSION['user_id'])) {
        $stmtDel = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmtDel->execute([$id]);
        header('Location: usuarios.php?msg=borrado');
        exit;
    } else {
        $error = "No puedes eliminar tu propia cuenta.";
    }
}

// Traer todos los usuarios
$stmt = $pdo->query("
    SELECT u.id, u.name, u.email, r.name AS role_name, u.created_at
    FROM users u
    JOIN roles r ON u.role_id = r.id
    ORDER BY u.created_at DESC
");
$usuarios = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h3>Gestión de Usuarios</h3>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'borrado'): ?>
            <div class="alert alert-success">Usuario eliminado correctamente.</div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <a 
          href="/hotel_reservas/roles/admin/usuarios_agregar.php" 
          class="btn btn-primary mb-3"
        >
          Agregar Usuario
        </a>

        <table class="table table-hover table-custom">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Registrado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo $u['role_name']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?></td>
                        <td>
                            <a 
                              href="/hotel_reservas/roles/admin/usuarios_agregar.php?edit_id=<?php echo $u['id']; ?>" 
                              class="btn btn-sm btn-secondary"
                            >
                              Editar
                            </a>
                            <a 
                              href="/hotel_reservas/roles/admin/usuarios.php?delete_id=<?php echo $u['id']; ?>"
                              onclick="return confirmarEliminacion('usuario');" 
                              class="btn btn-sm btn-danger"
                            >
                              Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
