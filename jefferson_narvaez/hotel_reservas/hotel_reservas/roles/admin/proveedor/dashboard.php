<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

if (!usuario_tiene_rol(['Supplier'])) {
    header('Location: /hotel_reservas/index.php');
    exit;
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2>Panel de Proveedor</h2>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.</p>

        <div class="row mt-4">
            <div class="col-md-4 mb-3">
                <div class="card card-custom p-3">
                    <h5>Ver Necesidades del Hotel</h5>
                    <p>Ofertar productos o servicios.</p>
                    <a 
                      href="/hotel_reservas/roles/proveedor/ofertas.php" 
                      class="btn btn-secondary"
                    >
                      Ir a Ofertas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
