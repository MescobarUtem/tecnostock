<?php
require_once 'Conexion.php';
include_once 'includes/header.php';

// Definir si el usuario actual es administrador
$esAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';

// Procesar formulario de creación de categoría (Solo Administrador)
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_categoria'])) {
    if (!$esAdmin) {
        $mensaje = '<div class="alert alert-danger">Acceso denegado. No tienes permisos para crear categorías.</div>';
    } else {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $estado = isset($_POST['estado']) ? 1 : 0;

        if (!empty($nombre)) {
            $stmt = $pdo->prepare("INSERT INTO categoria (nombre, descripcion, estado) VALUES (:nombre, :descripcion, :estado)");
            $stmt->execute([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'estado' => $estado
            ]);
            $mensaje = '<div class="alert alert-success">Categoría registrada exitosamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">El nombre de la categoría es obligatorio.</div>';
        }
    }
}

// Obtener todas las categorías
$stmt = $pdo->query("SELECT * FROM categoria ORDER BY id_categoria DESC");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Categorías</h2>
    <?php if ($esAdmin): ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="bi bi-plus-lg"></i> Nueva Categoría
        </button>
    <?php endif; ?>
</div>

<?= $mensaje ?>

<!-- Tabla de Categorías -->
<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categorias)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No hay categorías registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categorias as $cat): ?>
                        <tr>
                            <td><?= $cat['id_categoria'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($cat['nombre']) ?></td>
                            <td><?= htmlspecialchars($cat['descripcion']) ?></td>
                            <td>
                                <?php if ($cat['estado'] == 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($esAdmin): ?>
                                    <a href="categoria_eliminar.php?id=<?= $cat['id_categoria'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Deseas eliminar esta categoría?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted">Solo lectura</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Crear Categoría (Solo renderizado para Admin) -->
<?php if ($esAdmin): ?>
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="categorias.php">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Categoría</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre de la Categoría *</label>
            <input type="text" name="nombre" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"></textarea>
          </div>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="estado" id="estado" checked>
            <label class="form-check-label" for="estado">Estado Activo</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="crear_categoria" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>