<?php
// logout.php
session_start();
session_unset();
session_destroy();
header('Location: /hotel_reservas/login.php');
exit;
