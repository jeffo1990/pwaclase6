<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

if (!usuario_tiene_rol(['Supplier'])) {
    header('Location: /hotel_reservas/index.php');
    exit;
}

// Simulación de necesidades (en un proyecto real, vendrían de otra tabla)
$necesidades = [
    ['id' => 1, 'name' => 'Artículos de limpieza', 'quantity' => 50],
    ['id' => 2, 'name' => 'Alimentos perecibles', 'quantity' => 100],
    ['id' => 3, 'name' => 'Productos de baño', 'quantity' => 80],
];

$errores  = [];
$mensajes = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $necesidad_id = intval($_POST['necesidad_id'] ?? 0);
    $oferta_precio = floatval($_POST['oferta_precio'] ?? 0);

    if ($necesidad_id === 0 || $oferta_precio <= 0) {
        $errores[] = "Todos los campos son obligatorios y válidos.";
    } else {
        $nombreNecesidad = '';
        foreach ($necesidades as $n) {
            if ($n['id'] === $necesidad_id) {
                $nombreNecesidad = $n['name'];
                break;
            }
        }
        if ($nombreNecesidad === '') {
            $errores[] = "Necesidad inválida.";
        } else {
            $detalle = $nombreNecesidad . " - Precio ofertado: $" . number_format($oferta_precio, 2);
            $stmtIns = $pdo->prepare("
                INSERT INTO supplies (name, quantity, supplier_id)
                VALUES (?, ?, ?)
            ");
            $stmtIns->execute([$detalle, $necesidad_id, $_SESSION['user_id']]);
            $mensajes[] = "Oferta enviada para '$nombreNecesidad'.";
        }
    }
}

// Traer ofertas realizadas por este proveedor
$stmtOffers = $pdo->prepare("
    SELECT * FROM supplies
    WHERE supplier_id = ?
    ORDER BY created_at DESC
");
$stmtOffers->execute([$_SESSION['user_id']]);
$ofertas = $stmtOffers->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h3>Ofertas para Necesidades del Hotel</h3>

        <?php if (!empty($mensajes)): ?>
            <div class="alert alert-success">
                <ul class="mb-0">
                    <?php foreach ($mensajes as $msg): ?>
                        <li><?php echo $msg; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errores as $err): ?>
                        <li><?php echo $err; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card card-custom mb-4 p-4">
            <h5 class="mb-3">Nueva Oferta</h5>
            <form method="POST" action="ofertas.php">
                <div class="mb-3">
                    <label for="necesidad_id" class="form-label">Necesidad</label>
                    <select name="necesidad_id" id="necesidad_id" class="form-select">
                        <option value="0">-- Seleccione --</option>
                        <?php foreach ($necesidades as $n): ?>
                            <option value="<?php echo $n['id']; ?>">
                              <?php echo $n['name']; ?> (Cantidad: <?php echo $n['quantity']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="oferta_precio" class="form-label">Precio ofertado ($)</label>
                    <input 
                      type="number" 
                      step="0.01" 
                      name="oferta_precio" 
                      id="oferta_precio" 
                      class="form-control"
                    >
                </div>

                <button type="submit" class="btn btn-primary">Enviar Oferta</button>
            </form>
        </div>

        <h5>Mis Ofertas Realizadas</h5>
        <table class="table table-hover table-custom">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Oferta</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ofertas)): ?>
                    <tr>
                        <td colspan="3" class="text-center">Aún no has enviado ofertas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ofertas as $o): ?>
                        <tr>
                            <td><?php echo $o['id']; ?></td>
                            <td><?php echo htmlspecialchars($o['name']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($o['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
