<?php
require_once 'Conexion.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM categoria WHERE id_categoria = :id");
        $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        // En caso de estar asociada a algún producto con restricción
    }
}

header("Location: categorias.php");
exit;
?>