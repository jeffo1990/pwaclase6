<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay sesión activa, redirigir a login
if (!isset($_SESSION['user_id'])) {
    header('Location: /hotel_reservas/login.php');
    exit;
}

/**
 * Verifica si el usuario tiene uno de los roles permitidos.
 * @param array $roles_permitidos Ejemplo: ['Administrator', 'Receptionist']
 * @return bool
 */
function usuario_tiene_rol(array $roles_permitidos): bool {
    if (!isset($_SESSION['role_name'])) {
        return false;
    }
    return in_array($_SESSION['role_name'], $roles_permitidos, true);
}
