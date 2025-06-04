<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

if (!usuario_tiene_rol(['Hotel Manager'])) {
    header('Location: /hotel_reservas/index.php');
    exit;
}

// Eliminar habitación
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmtDel = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    $stmtDel->execute([$id]);
    header('Location: habitaciones.php?msg=borrado');
    exit;
}

// Traer habitaciones
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY room_number ASC");
$habitaciones = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h3>Gestión de Habitaciones</h3>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'borrado'): ?>
            <div class="alert alert-success">Habitación eliminada correctamente.</div>
        <?php endif; ?>

        <a 
          href="/hotel_reservas/roles/gerente/habitaciones_agregar.php" 
          class="btn btn-primary mb-3"
        >
          Agregar Habitación
        </a>

        <div class="row">
            <?php foreach ($habitaciones as $hab): ?>
                <div class="col-md-4 mb-4">
                    <div class="card card-custom">
                        <img 
                          src="/hotel_reservas/assets/img/habitaciones/<?php echo $hab['room_type']; ?>.jpg"
                          class="card-img-top room-img" 
                          alt="<?php echo $hab['room_type']; ?>"
                        >
                        <div class="card-body">
                            <h5 class="card-title">
                              Hab. <?php echo $hab['room_number']; ?> - <?php echo ucfirst($hab['room_type']); ?>
                            </h5>
                            <p class="card-text">Precio: $<?php echo number_format($hab['room_price'], 2); ?></p>
                            <p class="card-text">
                                Estado: 
                                <?php echo $hab['is_available'] 
                                    ? '<span class="badge bg-success">Disponible</span>' 
                                    : '<span class="badge bg-danger">Ocupada</span>'; 
                                ?>
                            </p>
                            <a 
                              href="/hotel_reservas/roles/gerente/habitaciones_agregar.php?edit_id=<?php echo $hab['id']; ?>" 
                              class="btn btn-sm btn-secondary"
                            >
                              Editar
                            </a>
                            <a 
                              href="/hotel_reservas/roles/gerente/habitaciones.php?delete_id=<?php echo $hab['id']; ?>"
                              onclick="return confirmarEliminacion('habitación');" 
                              class="btn btn-sm btn-danger"
                            >
                              Eliminar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
