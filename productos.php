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
            'codigo' => $codigo,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock_actual' => $stock_actual,
            'stock_minimo' => $stock_minimo,
            'estado' => $estado,
            'id_categoria' => $id_categoria
        ]);
        $mensaje = '<div class="alert alert-success">Producto registrado correctamente.</div>';
    } else {
        $mensaje = '<div class="alert alert-danger">Por favor completa los campos obligatorios (*).</div>';
    }
}

// Obtener productos con el nombre de su categoría
$stmt = $pdo->query("SELECT p.*, c.nombre AS categoria 
                      FROM producto p 
                      INNER JOIN categoria c ON p.id_categoria = c.id_categoria 
                      ORDER BY p.id_producto DESC");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías activas para el select del formulario
$categorias = $pdo->query("SELECT * FROM categoria WHERE estado = 1 ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Productos</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProducto">
        <i class="bi bi-plus-lg"></i> Nuevo Producto
    </button>
</div>

<?= $mensaje ?>

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
                    <tr>
                        <td colspan="7" class="text-center text-muted">No hay productos registrados.</td>
                    </tr>
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
                            <td>
                                <?= $p['estado'] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?>
                            </td>
                            <td class="text-end">
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
      <form method="POST" action="productos.php">
        <div class="modal-header">
          <h5 class="modal-title">Registrar Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <div class="col-md-6">
            <label class="form-label">Código *</label>
            <input type="text" name="codigo" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Nombre del Producto *</label>
            <input type="text" name="nombre" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Categoría *</label>
            <select name="id_categoria" class="form-select" required>
              <option value="">Seleccione una categoría...</option>
              <?php foreach ($categorias as $c): ?>
                <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Precio *</label>
            <input type="number" step="0.01" name="precio" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Stock Inicial *</label>
            <input type="number" name="stock_actual" class="form-control" value="0" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Stock Mínimo *</label>
            <input type="number" name="stock_minimo" class="form-control" value="5" required>
          </div>
          <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="2"></textarea>
          </div>
          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="estado" id="estadoProd" checked>
              <label class="form-check-label" for="estadoProd">Producto Activo</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="crear_producto" class="btn btn-primary">Guardar Producto</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once 'includes/footer.php'; ?>