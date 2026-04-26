<?php
session_start();

// Verificar si el usuario está logueado
if(isset($_SESSION['user_id'])) {
    // Redirigir según el rol
    if($_SESSION['user_rol'] === 'admin') {
        header("Location: src/pages/admin/dashboard.php");
        exit();
    } elseif($_SESSION['user_rol'] === 'usuario') {
        header("Location: src/pages/user/tienda.php");
        exit();
    }
}

// Si no está logueado, mostrar la landing page
include 'src/pages/landingpage.php';
?>