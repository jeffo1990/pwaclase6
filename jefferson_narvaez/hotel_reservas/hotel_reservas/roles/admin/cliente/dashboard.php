<?php
// roles/cliente/dashboard.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

if (!usuario_tiene_rol(['Customer'])) {
    header('Location: /hotel_reservas/index.php');
    exit;
}

// Traer habitaciones disponibles
$stmt = $pdo->query("SELECT * FROM rooms WHERE is_available = 1 ORDER BY room_price ASC");
$disponibles = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2>Panel de Cliente</h2>
        <p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>.</p>
        <hr>
    </div>
</div>

<div class="row">
    <!-- Botón a Mis Reservas -->
    <div class="col-md-4 mb-3">
        <div class="card card-custom p-3">
            <h5>Mis Reservas</h5>
            <p>Ver el estado de tus reservaciones.</p>
            <a 
              href="/hotel_reservas/roles/cliente/mis_reservas.php" 
              class="btn btn-secondary w-100"
            >
              Ver Mis Reservas
            </a>
        </div>
    </div>
</div>

<!-- SECCIÓN DE BÚSQUEDA DE HABITACIONES -->
<div class="row mt-4">
    <div class="col-12">
        <h4>Habitaciones Disponibles</h4>
        <p>Haz clic en “Reservar” para confirmar con fecha automática (hoy → mañana).</p>
    </div>
</div>

<div class="row">
    <?php if (empty($disponibles)): ?>
        <div class="col-12">
            <div class="alert alert-info">Por el momento no hay habitaciones disponibles.</div>
        </div>
    <?php else: ?>
        <?php foreach ($disponibles as $h): ?>
            <div class="col-md-4 mb-4">
                <div class="card card-custom">
                    <img 
                      src="/hotel_reservas/assets/img/habitaciones/<?php echo $h['room_type']; ?>.jpg"
                      class="card-img-top room-img" 
                      alt="<?php echo $h['room_type']; ?>"
                    >
                    <div class="card-body">
                        <h5 class="card-title">
                          Hab. <?php echo $h['room_number']; ?> - <?php echo ucfirst($h['room_type']); ?>
                        </h5>
                        <p class="card-text">Precio noche: $<?php echo number_format($h['room_price'], 2); ?></p>
                        <a 
                          href="/hotel_reservas/roles/cliente/mis_reservas.php?reservar=<?php echo $h['id']; ?>" 
                          class="btn btn-primary w-100"
                        >
                          Reservar Hoy→Mañana
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>
