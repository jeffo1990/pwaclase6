<?php
// roles/recepcionista/reservas.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

// Verificar que solo recepcionistas accedan
if (!usuario_tiene_rol(['Receptionist'])) {
    header('Location: /hotel_reservas/index.php');
    exit;
}

// 1) Proceso de Reserva
$errores = [];
$mensaje_exito = '';

// Si el formulario fue enviado por POST:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id     = intval($_POST['cliente_id'] ?? 0);
    $room_id        = intval($_POST['room_id'] ?? 0);
    $check_in_date  = $_POST['check_in_date'] ?? '';
    $check_out_date = $_POST['check_out_date'] ?? '';

    // Validaciones básicas
    if ($cliente_id === 0 || $room_id === 0 || $check_in_date === '' || $check_out_date === '') {
        $errores[] = "Todos los campos son obligatorios.";
    } elseif ($check_out_date <= $check_in_date) {
        $errores[] = "La fecha de salida debe ser mayor a la fecha de entrada.";
    }

    if (empty($errores)) {
        // Insertar en bookings
        $booking_date = date('Y-m-d H:i:s');
        $stmtIns = $pdo->prepare("
            INSERT INTO bookings (user_id, room_id, booking_date, check_in_date, check_out_date)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtIns->execute([$cliente_id, $room_id, $booking_date, $check_in_date, $check_out_date]);

        // Marcar la habitación como no disponible
        $stmtUpd = $pdo->prepare("UPDATE rooms SET is_available = 0 WHERE id = ?");
        $stmtUpd->execute([$room_id]);

        $mensaje_exito = "Reserva realizada con éxito para el cliente ID <strong>{$cliente_id}</strong>.";
    }
}

// 2) Obtener lista de Clientes (rol = Customer)
$stmtClientes = $pdo->prepare("
    SELECT id, name 
    FROM users 
    WHERE role_id = (SELECT id FROM roles WHERE name = 'Customer')
");
$stmtClientes->execute();
$clientes = $stmtClientes->fetchAll();

// 3) Obtener habitaciones disponibles
$stmtRooms = $pdo->query("SELECT * FROM rooms WHERE is_available = 1 ORDER BY room_number ASC");
$habitaciones = $stmtRooms->fetchAll();

// 4) Obtener todas las reservas para listado
$stmtRes = $pdo->query("
    SELECT b.id, u.name AS cliente, r.room_number, r.room_type, 
           b.check_in_date, b.check_out_date, b.booking_date
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    ORDER BY b.booking_date DESC
");
$reservas = $stmtRes->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2>Reservas (Recepcionista)</h2>
        <p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>.</p>
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
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- FORMULARIO DE RESERVA -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card card-custom p-4">
            <h5 class="mb-3">Nueva Reserva</h5>
            <form method="POST" action="/hotel_reservas/roles/recepcionista/reservas.php">
                <div class="mb-3">
                    <label for="cliente_id" class="form-label">Cliente</label>
                    <select name="cliente_id" id="cliente_id" class="form-select" required>
                        <option value="">-- Seleccione Cliente --</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?php echo $c['id']; ?>">
                                <?php echo htmlspecialchars($c['name']); ?> (ID: <?php echo $c['id']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="room_id" class="form-label">Hab. Disponible</label>
                    <select name="room_id" id="room_id" class="form-select" required>
                        <option value="">-- Seleccione Habitación --</option>
                        <?php foreach ($habitaciones as $h): ?>
                            <option value="<?php echo $h['id']; ?>">
                                Hab. <?php echo $h['room_number']; ?> - <?php echo ucfirst($h['room_type']); ?> 
                                ($<?php echo number_format($h['room_price'], 2); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="check_in_date" class="form-label">Fecha Entrada</label>
                        <input 
                          type="date" 
                          name="check_in_date" 
                          id="check_in_date" 
                          class="form-control" 
                          required
                        >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="check_out_date" class="form-label">Fecha Salida</label>
                        <input 
                          type="date" 
                          name="check_out_date" 
                          id="check_out_date" 
                          class="form-control" 
                          required
                        >
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Reservar</button>
            </form>
        </div>
    </div>

    <!-- LISTADO DE RESERVAS -->
    <div class="col-md-6">
        <h5>Listado de Reservas Recientes</h5>
        <div class="table-responsive">
            <table class="table table-hover table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Hab. Nº</th>
                        <th>Tipo</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Registrada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservas)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay reservas aún.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reservas as $r): ?>
                            <tr>
                                <td><?php echo $r['id']; ?></td>
                                <td><?php echo htmlspecialchars($r['cliente']); ?></td>
                                <td><?php echo $r['room_number']; ?></td>
                                <td><?php echo ucfirst($r['room_type']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($r['check_in_date'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($r['check_out_date'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($r['booking_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
