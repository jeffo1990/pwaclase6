<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

if (!usuario_tiene_rol(['Hotel Manager'])) {
    header('Location: /hotel_reservas/index.php');
    exit;
}

$errores = [];
$modo   = 'crear';
$room_types = ['estandar', 'deluxe', 'suite'];
$habitacion = [
    'room_number' => '',
    'room_type'   => '',
    'room_price'  => '',
    'is_available'=> 1
];

if (isset($_GET['edit_id'])) {
    $modo = 'editar';
    $edit_id = intval($_GET['edit_id']);
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$edit_id]);
    $habitacion = $stmt->fetch();
    if (!$habitacion) {
        header('Location: habitaciones.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number  = intval($_POST['room_number'] ?? 0);
    $room_type    = trim($_POST['room_type'] ?? '');
    $room_price   = floatval($_POST['room_price'] ?? 0);
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    if ($room_number === 0 || $room_type === '' || $room_price <= 0) {
        $errores[] = "Todos los campos son obligatorios y válidos.";
    } elseif (!in_array($room_type, $room_types)) {
        $errores[] = "Tipo de habitación inválido.";
    }

    if (empty($errores)) {
        if ($modo === 'editar') {
            $stmtUpd = $pdo->prepare("
                UPDATE rooms 
                SET room_number = ?, room_type = ?, room_price = ?, is_available = ?
                WHERE id = ?
            ");
            $stmtUpd->execute([$room_number, $room_type, $room_price, $is_available, $edit_id]);
            header('Location: habitaciones.php?msg=editado');
            exit;
        } else {
            // Verificar número único
            $stmtChk = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ?");
            $stmtChk->execute([$room_number]);
            if ($stmtChk->rowCount() > 0) {
                $errores[] = "El número de habitación ya existe.";
            } else {
                $stmtIns = $pdo->prepare("
                    INSERT INTO rooms (room_number, room_type, room_price, is_available)
                    VALUES (?, ?, ?, ?)
                ");
                $stmtIns->execute([$room_number, $room_type, $room_price, $is_available]);
                header('Location: habitaciones.php?msg=agregado');
                exit;
            }
        }
    }
}

// Si en modo editar, precargar variables
if ($modo === 'editar' && isset($habitacion)) {
    $room_number  = $habitacion['room_number'];
    $room_type    = $habitacion['room_type'];
    $room_price   = $habitacion['room_price'];
    $is_available = $habitacion['is_available'];
}

include '../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-custom p-4">
            <h4 class="mb-4">
                <?php echo ($modo === 'editar') ? "Editar Habitación" : "Agregar Habitación"; ?>
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
                    <label for="room_number" class="form-label">Número de Habitación</label>
                    <input 
                      type="number" 
                      name="room_number" 
                      id="room_number" 
                      class="form-control"
                      value="<?php echo htmlspecialchars($room_number ?? ''); ?>"
                    >
                </div>

                <div class="mb-3">
                    <label for="room_type" class="form-label">Tipo de Habitación</label>
                    <select name="room_type" id="room_type" class="form-select">
                        <option value="">-- Seleccione --</option>
                        <?php foreach ($room_types as $tipo): ?>
                            <option 
                              value="<?php echo $tipo; ?>"
                              <?php 
                                if (isset($room_type) && $room_type === $tipo) {
                                    echo 'selected';
                                }
                              ?>
                            >
                              <?php echo ucfirst($tipo); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="room_price" class="form-label">Precio ($)</label>
                    <input 
                      type="number" 
                      step="0.01" 
                      name="room_price" 
                      id="room_price" 
                      class="form-control"
                      value="<?php echo htmlspecialchars($room_price ?? ''); ?>"
                    >
                </div>

                <?php if ($modo === 'editar'): ?>
                    <div class="form-check mb-3">
                        <input 
                          class="form-check-input" 
                          type="checkbox" 
                          name="is_available" 
                          id="is_available"
                          <?php echo ($is_available) ? 'checked' : ''; ?>
                        >
                        <label class="form-check-label" for="is_available">
                            Disponible
                        </label>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary w-100">
                    <?php echo ($modo === 'editar') ? "Actualizar" : "Agregar"; ?>
                </button>
                <a 
                  href="/hotel_reservas/roles/gerente/habitaciones.php" 
                  class="btn btn-secondary w-100 mt-2"
                >
                  Cancelar
                </a>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
