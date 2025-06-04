<?php
// reservas.php
require_once 'config/db.php';
require_once 'includes/auth.php'; // Verifica que el usuario esté logueado

// 1) Procesamiento del formulario de reserva
$errores = [];
$mensaje_exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id        = intval($_SESSION['user_id']);
    $room_id        = intval($_POST['room_id'] ?? 0);
    $check_in_date  = $_POST['check_in_date']  ?? '';
    $check_out_date = $_POST['check_out_date'] ?? '';

    // Validaciones básicas
    if ($room_id === 0 || $check_in_date === '' || $check_out_date === '') {
        $errores[] = "Debes seleccionar habitación, fecha de entrada y fecha de salida.";
    } elseif ($check_out_date <= $check_in_date) {
        $errores[] = "La fecha de salida debe ser posterior a la fecha de entrada.";
    }

    // Verificar disponibilidad
    if (empty($errores)) {
        $stmtChk = $pdo->prepare("SELECT is_available FROM rooms WHERE id = ?");
        $stmtChk->execute([$room_id]);
        $habit = $stmtChk->fetch();
        if (!$habit || !$habit['is_available']) {
            $errores[] = "La habitación seleccionada ya no está disponible.";
        }
    }

    // Insertar reserva y marcar habitación ocupada
    if (empty($errores)) {
        $booking_date = date('Y-m-d H:i:s');
        $stmtIns = $pdo->prepare("
            INSERT INTO bookings 
              (user_id, room_id, booking_date, check_in_date, check_out_date)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtIns->execute([$user_id, $room_id, $booking_date, $check_in_date, $check_out_date]);

        $stmtUpd = $pdo->prepare("UPDATE rooms SET is_available = 0 WHERE id = ?");
        $stmtUpd->execute([$room_id]);

        $mensaje_exito = "¡Reserva exitosa! (Usuario ID {$user_id}, Habitación ID {$room_id})";
    }
}

// 2) Obtener todas las habitaciones
$stmtRoomsAll = $pdo->query("SELECT * FROM rooms ORDER BY room_number ASC");
$habitaciones = $stmtRoomsAll->fetchAll();

// 3) Filtrar las disponibles
$habitaciones_disp = array_filter($habitaciones, fn($h) => $h['is_available']);

// 4) Obtener todas las reservas (JOIN con usuarios y habitaciones)
$stmtRes = $pdo->query("
    SELECT 
      b.id,
      u.name     AS cliente,
      r.room_number,
      r.room_type,
      r.room_price,
      b.check_in_date,
      b.check_out_date,
      b.booking_date
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    ORDER BY b.booking_date DESC
");
$reservas = $stmtRes->fetchAll();

// 5) Mapeo estático de URLs de imágenes por número de habitación
//    Sólo reemplaza los valores 'TU_URL_AQUI' con tus rutas o URLs.
$image_urls = [
    101 => 'https://i.pinimg.com/originals/52/18/a7/5218a738ab37026e7147c9e1692f2b47.jpg',
    102 => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTrgBiPmcEFnWhu0Uk4KMNfGdzVLSYWnX8WLQ&s',
    201 => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQxRGTwkQqZ9a3FdJUNEE1SuJ4Tp5HDanh8-A&s',
    202 => 'https://i.pinimg.com/originals/dd/86/0e/dd860ed604a02bcd57529eaef7bbe342.jpg',
    301 => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRSNnIqRxvjEvwM_v5bnT8SlRL3ujT95XIkCA&s',
    302 => 'https://img.freepik.com/foto-gratis/interior-dormitorio-lujo-muebles-ricos-vistas-panoramicas-cubierta-huelga_1258-111483.jpg?semt=ais_items_boosted&w=740',
    // Si agregas más habitaciones, extiende este arreglo...
];

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12 text-center">
        <h2>Reservas de Hotel</h2>
        <p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>. Aquí puedes:
            <ul class="list-inline">
                <li class="list-inline-item">• Ver habitaciones disponibles</li>
                <li class="list-inline-item">• Reservar</li>
                <li class="list-inline-item">• Consultar todas las reservas</li>
                <li class="list-inline-item">• Contactar al personal</li>
            </ul>
        </p>
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

<!-- SECCIÓN: Habitaciones Disponibles -->
<div class="row mt-4">
    <div class="col-12">
        <h5>🔑 Habitaciones Disponibles</h5>
    </div>
</div>
<div class="row">
    <?php if (empty($habitaciones_disp)): ?>
        <div class="col-12">
            <div class="alert alert-info">No hay habitaciones disponibles en este momento.</div>
        </div>
    <?php else: ?>
        <?php foreach ($habitaciones_disp as $h): ?>
            <?php
                // Buscar URL de imagen en el mapeo estático
                $url_img = $image_urls[$h['room_number']] ?? null;

                // Si no está definido en el arreglo, intentar imagen por tipo
                if (!$url_img) {
                    $ruta_tipo = __DIR__ . "/assets/img/habitaciones/{$h['room_type']}.jpg";
                    if (file_exists($ruta_tipo)) {
                        $url_img = "assets/img/habitaciones/{$h['room_type']}.jpg";
                    } else {
                        $url_img = "https://via.placeholder.com/400x250?text=Sin+Imagen";
                    }
                }
            ?>
            <div class="col-md-4 mb-4">
                <div class="card card-custom h-100">
                    <img 
                      src="<?php echo htmlspecialchars($url_img); ?>" 
                      class="card-img-top room-img" 
                      alt="Habitación <?php echo $h['room_number']; ?>"
                    >
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Hab. <?php echo $h['room_number']; ?></h5>
                        <p class="card-text mb-1">
                            <strong>Tipo:</strong> <?php echo ucfirst($h['room_type']); ?>
                        </p>
                        <p class="card-text mb-3">
                            <strong>Precio:</strong> $<?php echo number_format($h['room_price'], 2); ?> / noche
                        </p>
                        <button 
                          class="btn btn-primary mt-auto"
                          onclick="
                            document.getElementById('room_id').value = <?php echo $h['id']; ?>;
                            window.location.href = '#formReserva';
                          "
                        >
                          Seleccionar para Reservar
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- FORMULARIO DE RESERVA -->
<div class="row mt-5" id="formReserva">
    <div class="col-md-6 mb-4">
        <div class="card card-custom p-4">
            <h5 class="mb-3">📝 Nueva Reserva</h5>
            <form method="POST" action="reservas.php">
                <div class="mb-3">
                    <label for="room_id" class="form-label">Selecciona Habitación</label>
                    <select 
                      name="room_id" 
                      id="room_id" 
                      class="form-select" 
                      required
                    >
                        <option value="">-- Habitación Disponible --</option>
                        <?php foreach ($habitaciones_disp as $h): ?>
                            <option value="<?php echo $h['id']; ?>">
                                Hab. <?php echo $h['room_number']; ?> — 
                                <?php echo ucfirst($h['room_type']); ?> 
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

    <!-- TABLA: Todas las Reservas -->
    <div class="col-md-6">
        <h5>📋 Todas las Reservas</h5>
        <div class="table-responsive">
            <table class="table table-hover table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Hab. Nº</th>
                        <th>Tipo</th>
                        <th>Precio ($)</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Registrada En</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservas)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay reservas registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reservas as $r): ?>
                            <tr>
                                <td><?php echo $r['id']; ?></td>
                                <td><?php echo htmlspecialchars($r['cliente']); ?></td>
                                <td><?php echo $r['room_number']; ?></td>
                                <td><?php echo ucfirst($r['room_type']); ?></td>
                                <td>$<?php echo number_format($r['room_price'], 2); ?></td>
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

<!-- SECCIÓN: Contacto del Personal -->
<div class="row mt-5">
    <div class="col-12">
        <h5>📞 Contacto del Personal</h5>
        <p>Si necesitas asistencia, comunícate con:</p>
    </div>
</div>
<div class="row">
    <!-- Gerente -->
    <div class="col-md-4 mb-4">
        <div class="card card-custom h-100 text-center p-3">
            <?php
                // Ruta relativa para la imagen del gerente:
                $imgGerente = __DIR__ . "/assets/img/personas/gerente.jpg";
                $urlGerente = "assets/img/personas/gerente.jpg";
                if (!file_exists($imgGerente)) {
                    $urlGerente = "https://cms.usanmarcos.ac.cr/sites/default/files/styles/large/public/2022-11/que-hace-un-gerente.png?itok=9Bp9ySk7";
                }
            ?>
            <img 
              src="<?php echo htmlspecialchars($urlGerente); ?>" 
              class="rounded-circle mx-auto d-block mb-3" 
              style="width: 100px; height: 100px; object-fit: cover;" 
              alt="Gerente"
            >
            <div class="card-body d-flex flex-column">
                <h6 class="card-title">Gerente</h6>
                <p class="mb-1"><strong>Email:</strong> gerente@hotel.com</p>
                <p class="mb-1"><strong>Teléfono:</strong> +593 9 1234 5678</p>
                <a href="mailto:gerente@hotel.com" class="btn btn-outline-primary mt-auto">Enviar Correo</a>
            </div>
        </div>
    </div>

    <!-- Recepcionista -->
    <div class="col-md-4 mb-4">
        <div class="card card-custom h-100 text-center p-3">
            <?php
                // Ruta relativa para la imagen de la recepcionista:
                $imgRecep = __DIR__ . "/assets/img/personas/recepcionista.jpg";
                $urlRecep = "assets/img/personas/recepcionista.jpg";
                if (!file_exists($imgRecep)) {
                    $urlRecep = "https://cdn.prod.website-files.com/65489618a9e91669c78068e2/67fca08f5169a1ca9fc9e56f_14-habilidades-de-una-recepcionista.webp";
                }
            ?>
            <img 
              src="<?php echo htmlspecialchars($urlRecep); ?>" 
              class="rounded-circle mx-auto d-block mb-3" 
              style="width: 100px; height: 100px; object-fit: cover;" 
              alt="Recepcionista"
            >
            <div class="card-body d-flex flex-column">
                <h6 class="card-title">Recepcionista</h6>
                <p class="mb-1"><strong>Email:</strong> recepcion@hotel.com</p>
                <p class="mb-1"><strong>Teléfono:</strong> +593 9 8765 4321</p>
                <a href="mailto:recepcion@hotel.com" class="btn btn-outline-primary mt-auto">Enviar Correo</a>
            </div>
        </div>
    </div>

    <!-- Proveedor -->
    <div class="col-md-4 mb-4">
        <div class="card card-custom h-100 text-center p-3">
            <?php
                // Ruta relativa para la imagen del proveedor:
                $imgProv = __DIR__ . "/assets/img/personas/proveedor.jpg";
                $urlProv = "assets/img/personas/proveedor.jpg";
                if (!file_exists($imgProv)) {
                    $urlProv = "https://colormake.com/wp-content/uploads/2016/08/busqueda-y-seleccion-de-proveedores.jpg";
                }
            ?>
            <img 
              src="<?php echo htmlspecialchars($urlProv); ?>" 
              class="rounded-circle mx-auto d-block mb-3" 
              style="width: 100px; height: 100px; object-fit: cover;" 
              alt="Proveedor"
            >
            <div class="card-body d-flex flex-column">
                <h6 class="card-title">Proveedor</h6>
                <p class="mb-1"><strong>Email:</strong> proveedor@hotel.com</p>
                <p class="mb-1"><strong>Teléfono:</strong> +593 9 1122 3344</p>
                <a href="mailto:proveedor@hotel.com" class="btn btn-outline-primary mt-auto">Enviar Correo</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
