<?php
session_start();

// Si no ha iniciado sesión o no es administrador, denegar acceso
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: productos.php?error=acceso_denegado');
    exit;
}

require_once 'Conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = $_POST['id_producto'];
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock_minimo = $_POST['stock_minimo'];
    $id_categoria = $_POST['id_categoria'];
    
    // Si el checkbox de estado está marcado en el modal, será 1 (activo), si no, 0 (inactivo/desactivado)
    $estado = isset($_POST['estado']) ? 1 : 0;

    if (!empty($id_producto) && !empty($codigo) && !empty($nombre) && !empty($id_categoria)) {
        try {
            $stmt = $pdo->prepare("UPDATE producto 
                                   SET codigo = :codigo, nombre = :nombre, descripcion = :descripcion, 
                                       precio = :precio, stock_minimo = :stock_minimo, 
                                       estado = :estado, id_categoria = :id_categoria 
                                   WHERE id_producto = :id_producto");
            $stmt->execute([
                'codigo' => $codigo,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'precio' => $precio,
                'stock_minimo' => $stock_minimo,
                'estado' => $estado,
                'id_categoria' => $id_categoria,
                'id_producto' => $id_producto
            ]);
        } catch (PDOException $e) {
            die("Error al actualizar el producto: " . $e->getMessage());
        }
    }
    
    // Redirigir de vuelta a la página principal de productos
    header('Location: productos.php');
    exit;
}
?>