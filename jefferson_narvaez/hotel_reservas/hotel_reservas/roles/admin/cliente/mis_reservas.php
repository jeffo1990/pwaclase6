<?php
// roles/cliente/mis_reservas.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

if (!usuario_tiene_rol(['Customer'])) {
    header('Location: /hotel_reservas/index.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);
$errores = [];
$mensaje_exito = '';

// 1) Si llega el parámetro ?reservar=ID, generar reserva automática
if (isset($_GET['reservar'])) {
    $room_id = intval($_GET['reservar']);

    // Verificar que la habitación siga disponible
    $stmtChk = $pdo->prepare("SELECT is_available FROM rooms WHERE id = ?");
    $stmtChk->execute([$room_id]);
    $habit = $stmtChk->fetch();

    if ($habit && $habit['is_available']) {
        $check_in_date  = date('Y-m-d');                     // Hoy
        $check_out_date = date('Y-m-d', strtotime('+1 day')); // Mañana
        $booking_date   = date('Y-m-d H:i:s');

        // Insertar en bookings
        $stmtIns = $pdo->prepare("
            INSERT INTO bookings (user_id, room_id, booking_date, check_in_date, check_out_date)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtIns->execute([$user_id, $room_id, $booking_date, $check_in_date, $check_out_date]);

        // Marcar habitación como no disponible
        $stmtUpd = $pdo->prepare("UPDATE rooms SET is_available = 0 WHERE id = ?");
        $stmtUpd->execute([$room_id]);

        $mensaje_exito = "¡Reserva exitosa! Habitación ID <strong>{$room_id}</strong> reservada de {$check_in_date} a {$check_out_date}.";
    } else {
        $errores[] = "Lo sentimos, la habitación ya no está disponible.";
    }
}

// 2) Obtener todas las reservas de este cliente
$stmt = $pdo->prepare("
    SELECT b.id, r.room_number, r.room_type, 
           b.check_in_date, b.check_out_date, b.booking_date
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->execute([$user_id]);
$misReservas = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2>Mis Reservas</h2>
        <p>Cliente: <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></p>
        <hr>
    </div>
</div>

<?php if ($mensaje_exito): ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success"><?php echo $mensaje_exito; ?></div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($errores)): ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errores as $err): ?>
                        <li><?php echo $err; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- TABLA DE RESERVAS DEL CLIENTE -->
<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-hover table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hab. Nº</th>
                        <th>Tipo</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Reservada En</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($misReservas)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No tienes reservas registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($misReservas as $res): ?>
                            <tr>
                                <td><?php echo $res['id']; ?></td>
                                <td><?php echo $res['room_number']; ?></td>
                                <td><?php echo ucfirst($res['room_type']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['check_in_date'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['check_out_date'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($res['booking_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
