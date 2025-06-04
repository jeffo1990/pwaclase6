<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

if (!usuario_tiene_rol(['Hotel Manager'])) {
    header('Location: /hotel_reservas/index.php');
    exit;
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2>Panel de Gerente de Hotel</h2>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.</p>

        <div class="row mt-4">
            <div class="col-md-4 mb-3">
                <div class="card card-custom p-3">
                    <h5>Gestión de Habitaciones</h5>
                    <p>Añadir, editar y eliminar habitaciones.</p>
                    <a 
                      href="/hotel_reservas/roles/gerente/habitaciones.php" 
                      class="btn btn-secondary"
                    >
                      Ir a Habitaciones
                    </a>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card card-custom p-3">
                    <h5>Ver Reservas (Próximamente)</h5>
                    <p>Revisar estado de reservas.</p>
                    <button class="btn btn-secondary" disabled>Ver Reservas</button>
                    <small class="text-muted">En desarrollo</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
