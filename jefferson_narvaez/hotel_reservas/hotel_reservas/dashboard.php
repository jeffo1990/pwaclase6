<?php
// dashboard.php
require_once 'config/db.php';
require_once 'includes/auth.php'; 

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12 text-center">
        <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
        <p>Aquí puedes acceder a todo el sistema de reservas sin importar tu rol.</p>
        <hr>
    </div>
</div>

<div class="row mt-3">
    <!-- ÚNICO ACCESO: Reservas -->
    <div class="col-md-4 offset-md-4 mb-3">
        <div class="card card-custom p-3 text-center">
            <h5>Sistema de Reservas</h5>
            <p>Ver habitaciones, reservar y consultar todas las reservas.</p>
            <a href="/hotel_reservas/reservas.php" class="btn btn-secondary w-100">
                Ir a Reservas
            </a>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12 text-center">
        <a href="/hotel_reservas/logout.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
