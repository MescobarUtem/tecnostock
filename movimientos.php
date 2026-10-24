<?php
require_once 'Conexion.php';
include_once 'includes/header.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_movimiento'])) {
    $id_producto = $_POST['id_producto'];
    $tipo = $_POST['tipo'];
    $cantidad = (int)$_POST['cantidad'];
    $observacion = trim($_POST['observacion']);
    $id_usuario = $_SESSION['id_usuario'];

    if (!empty($id_producto) && $cantidad > 0) {
        try {
            $pdo->beginTransaction();

            // Insertar el movimiento
            $stmt = $pdo->prepare("INSERT INTO movimiento (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario) 
                                    VALUES (NOW(), :tipo, :cantidad, :observacion, :id_producto, :id_usuario)");
            $stmt->execute([
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'observacion' => $observacion,
                'id_producto' => $id_producto,
                'id_usuario' => $id_usuario
            ]);

            // Actualizar stock del producto
            if ($tipo === 'entrada') {
                $update = $pdo->prepare("UPDATE producto SET stock_actual = stock_actual + :cant WHERE id_producto = :id");
            } else {
                $update = $pdo->prepare("UPDATE producto SET stock_actual = stock_actual - :cant WHERE id_producto = :id");
            }
            $update->execute(['cant' => $cantidad, 'id' => $id_producto]);

            $pdo->commit();
            $mensaje = '<div class="alert alert-success">Movimiento de inventario registrado con éxito.</div>';
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje = '<div class="alert alert-danger">Error al procesar el movimiento: ' . $e->getMessage() . '</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-danger">Selecciona un producto y asegura una cantidad válida.</div>';
    }
}

// Consultar historial de movimientos
$stmt = $pdo->query("SELECT m.*, p.nombre AS producto, u.nombre AS usuario 
                      FROM movimiento m 
                      INNER JOIN producto p ON m.id_producto = p.id_producto 
                      INNER JOIN usuario u ON m.id_usuario = u.id_usuario 
                      ORDER BY m.id_movimiento DESC");
$movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Productos para el select
$productos = $pdo->query("SELECT * FROM producto WHERE estado = 1 ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Movimientos de Inventario</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMovimiento">
        <i class="bi bi-arrow-left-right"></i> Registrar Entrada / Salida
    </button>
</div>

<?= $mensaje ?>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Tipo</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Usuario</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay movimientos registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $m): ?>
                        <tr>
                            <td><?= $m['fecha_hora'] ?></td>
                            <td>
                                <?php if ($m['tipo'] === 'entrada'): ?>
                                    <span class="badge bg-success"><i class="bi bi-arrow-down-circle"></i> Entrada</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="bi bi-arrow-up-circle"></i> Salida</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($m['producto']) ?></td>
                            <td><?= $m['cantidad'] ?></td>
                            <td><?= htmlspecialchars($m['usuario']) ?></td>
                            <td><?= htmlspecialchars($m['observacion']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Crear Movimiento -->
<div class="modal fade" id="modalMovimiento" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="movimientos.php" class="needs-validation" novalidate>
        <div class="modal-header">
          <h5 class="modal-title">Registrar Movimiento de Stock</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <div class="col-12">
            <label class="form-label">Producto *</label>
            <select name="id_producto" class="form-select" required>
              <option value="">Seleccione un producto...</option>
              <?php foreach ($productos as $prod): ?>
                <option value="<?= $prod['id_producto'] ?>"><?= htmlspecialchars($prod['nombre']) ?> (Stock actual: <?= $prod['stock_actual'] ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tipo de Movimiento *</label>
            <select name="tipo" class="form-select" required>
              <option value="entrada">Entrada (+)</option>
              <option value="salida">Salida (-)</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Cantidad *</label>
            <input type="number" min="1" name="cantidad" class="form-control" value="1" required>
          </div>
          <div class="col-12">
            <label class="form-label">Observación</label>
            <textarea name="observacion" class="form-control" rows="2" placeholder="Ej: Compra de lote, venta a cliente..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="registrar_movimiento" class="btn btn-primary">Guardar Movimiento</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once 'includes/footer.php'; ?>