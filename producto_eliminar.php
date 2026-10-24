<?php
session_start();

// Si no ha iniciado sesión o no es administrador, denegar acceso
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: productos.php?error=acceso_denegado');
    exit;
}

require_once 'Conexion.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM producto WHERE id_producto = :id");
        $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        // En caso de estar vinculado a movimientos existentes por restricción FK
    }
}

header("Location: productos.php");
exit;
?>