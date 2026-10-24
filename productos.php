<?php
require_once 'Conexion.php';
include_once 'includes/header.php';

$mensaje = '';

// Registrar nuevo producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_producto'])) {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];
    $id_categoria = $_POST['id_categoria'];
    $estado = isset($_POST['estado']) ? 1 : 0;

    if (!empty($codigo) && !empty($nombre) && !empty($id_categoria)) {
        $stmt = $pdo->prepare("INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, estado, id_categoria) 
                                VALUES (:codigo, :nombre, :descripcion, :precio, :stock_actual, :stock_minimo, :estado, :id_categoria)");
        $stmt->execute([
            'codigo' => $codigo, 'nombre' => $nombre, 'descripcion' => $descripcion,
            'precio' => $precio, 'stock_actual' => $stock_actual, 'stock_minimo' => $stock_minimo,
            'estado' => $estado, 'id_categoria' => $id_categoria
        ]);
        $mensaje = '<div class="alert alert-success">Producto registrado correctamente.</div>';
    } else {
        $mensaje = '<div class="alert alert-danger">Por favor completa los campos obligatorios.</div>';
    }
}

// Lógica de búsqueda
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$sql = "SELECT p.*, c.nombre AS categoria 
        FROM producto p 
        INNER JOIN categoria c ON p.id_categoria = c.id_categoria";
if ($buscar) {
    $sql .= " WHERE p.nombre LIKE :buscar OR p.codigo LIKE :buscar";
}
$sql .= " ORDER BY p.id_producto DESC";

$stmt = $pdo->prepare($sql);
if ($buscar) {
    $stmt->execute(['buscar' => "%$buscar%"]);
} else {
    $stmt->execute();
}
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categorías para los select
$categorias = $pdo->query("SELECT * FROM categoria WHERE estado = 1 ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Productos</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProducto">
        <i class="bi bi-plus-lg"></i> Nuevo Producto
    </button>
</div>

<!-- Barra de búsqueda -->
<form method="GET" action="productos.php" class="mb-4 d-flex">
    <input type="text" name="buscar" class="form-control me-2" placeholder="Buscar por código o nombre..." value="<?= htmlspecialchars($buscar) ?>">
    <button type="submit" class="btn btn-outline-secondary">Buscar</button>
    <?php if ($buscar): ?>
        <a href="productos.php" class="btn btn-outline-danger ms-2">Limpiar</a>
    <?php endif; ?>
</form>
<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productos)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No se encontraron productos.</td></tr>
                <?php else: ?>
                    <?php foreach ($productos as $p): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                            <td class="fw-bold"><?= htmlspecialchars($p['nombre']) ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($p['categoria']) ?></span></td>
                            <td>$<?= number_format($p['precio'], 2) ?></td>
                            <td>
                                <?php if ($p['stock_actual'] <= $p['stock_minimo']): ?>
                                    <span class="badge bg-danger"><?= $p['stock_actual'] ?> (Mín: <?= $p['stock_minimo'] ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= $p['stock_actual'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $p['estado'] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?></td>
                            <td class="text-end">
                                <!-- Botón Editar -->
                                <button type="button" class="btn btn-sm btn-outline-warning me-1 btn-editar"
                                    data-id="<?= $p['id_producto'] ?>" data-codigo="<?= htmlspecialchars($p['codigo']) ?>"
                                    data-nombre="<?= htmlspecialchars($p['nombre']) ?>" data-categoria="<?= $p['id_categoria'] ?>"
                                    data-precio="<?= $p['precio'] ?>" data-minimo="<?= $p['stock_minimo'] ?>"
                                    data-desc="<?= htmlspecialchars($p['descripcion']) ?>" data-estado="<?= $p['estado'] ?>"
                                    data-bs-toggle="modal" data-bs-target="#modalEditarProducto">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <!-- Botón Eliminar -->
                                <a href="producto_eliminar.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar producto?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Crear Producto -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="productos.php" class="needs-validation" novalidate>
        <div class="modal-header"><h5 class="modal-title">Registrar Producto</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body row g-3">
          <div class="col-md-6"><label>Código *</label><input type="text" name="codigo" class="form-control" required></div>
          <div class="col-md-6"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required></div>
          <div class="col-md-6"><label>Categoría *</label><select name="id_categoria" class="form-select" required><option value="">Seleccione...</option><?php foreach ($categorias as $c): ?><option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?></select></div>
          <div class="col-md-6"><label>Precio *</label><input type="number" step="0.01" name="precio" class="form-control" required></div>
          <div class="col-md-6"><label>Stock Inicial *</label><input type="number" name="stock_actual" class="form-control" value="0" required></div>
          <div class="col-md-6"><label>Stock Mínimo *</label><input type="number" name="stock_minimo" class="form-control" value="5" required></div>
          <div class="col-12"><label>Descripción</label><textarea name="descripcion" class="form-control" rows="2"></textarea></div>
          <div class="col-12"><input class="form-check-input" type="checkbox" name="estado" id="estadoProd" checked> <label>Producto Activo</label></div>
        </div>
        <div class="modal-footer"><button type="submit" name="crear_producto" class="btn btn-primary">Guardar</button></div>
      </form>
    </div>
  </div>
</div>
<!-- Modal Editar Producto -->
<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="producto_editar.php" class="needs-validation" novalidate>
        <div class="modal-header"><h5 class="modal-title">Editar Producto</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body row g-3">
          <input type="hidden" name="id_producto" id="edit_id_producto">
          <div class="col-md-6"><label>Código *</label><input type="text" name="codigo" id="edit_codigo" class="form-control" required></div>
          <div class="col-md-6"><label>Nombre *</label><input type="text" name="nombre" id="edit_nombre" class="form-control" required></div>
          <div class="col-md-6"><label>Categoría *</label><select name="id_categoria" id="edit_categoria" class="form-select" required><?php foreach ($categorias as $c): ?><option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?></select></div>
          <div class="col-md-6"><label>Precio *</label><input type="number" step="0.01" name="precio" id="edit_precio" min="0" class="form-control" required></div>
          <div class="col-md-6"><label>Stock Mínimo *</label><input type="number" name="stock_minimo" id="edit_stock_minimo" min="0" class="form-control" required></div>
          <div class="col-12"><label>Descripción</label><textarea name="descripcion" id="edit_descripcion" class="form-control" rows="2"></textarea></div>
          <div class="col-12"><input class="form-check-input" type="checkbox" name="estado" id="edit_estado"> <label>Producto Activo</label><small class="d-block text-muted">Desactiva el producto si ya no se venderá, sin perder su historial.</small></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-warning">Actualizar Producto</button></div>
      </form>
    </div>
  </div>
</div>

<script>
// Script para cargar los datos en el modal de edición
document.addEventListener('DOMContentLoaded', () => {
    const botonesEditar = document.querySelectorAll('.btn-editar');
    botonesEditar.forEach(boton => {
        boton.addEventListener('click', function() {
            document.getElementById('edit_id_producto').value = this.dataset.id;
            document.getElementById('edit_codigo').value = this.dataset.codigo;
            document.getElementById('edit_nombre').value = this.dataset.nombre;
            document.getElementById('edit_categoria').value = this.dataset.categoria;
            document.getElementById('edit_precio').value = this.dataset.precio;
            document.getElementById('edit_stock_minimo').value = this.dataset.minimo;
            document.getElementById('edit_descripcion').value = this.dataset.desc;
            document.getElementById('edit_estado').checked = this.dataset.estado == '1';
        });
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>  