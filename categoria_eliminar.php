<?php
session_start();

// Si no ha iniciado sesión o no es administrador, denegar acceso
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: categorias.php?error=acceso_denegado');
    exit;
}

require_once 'Conexion.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM categoria WHERE id_categoria = :id");
        $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        // En caso de estar asociada a algún producto con restricción FK
    }
}

header("Location: categorias.php");
exit;
?>