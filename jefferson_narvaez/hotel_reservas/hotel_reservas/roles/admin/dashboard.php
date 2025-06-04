<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

// Verificar rol
if (!usuario_tiene_rol(['Administrator'])) {
    header('Location: /hotel_reservas/index.php');
    exit;
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2>Panel de Administrador</h2>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.</p>

        <div class="row mt-4">
            <div class="col-md-4 mb-3">
                <div class="card card-custom p-3">
                    <h5>Gestión de Usuarios</h5>
                    <p>Añadir, editar y eliminar usuarios del sistema.</p>
                    <a 
                      href="/hotel_reservas/roles/admin/usuarios.php" 
                      class="btn btn-secondary"
                    >
                      Ir a Usuarios
                    </a>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card card-custom p-3">
                    <h5>Reportes (Próximamente)</h5>
                    <p>Estadísticas y auditoría de actividad.</p>
                    <button class="btn btn-secondary" disabled>Ver Reportes</button>
                    <small class="text-muted">En desarrollo</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
