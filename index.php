<?php
require_once 'Conexion.php';
include_once 'includes/header.php';

// Consultas rápidas para los indicadores del dashboard
$totalProd = $pdo->query("SELECT COUNT(*) FROM producto WHERE estado = 1")->fetchColumn();
$totalCat = $pdo->query("SELECT COUNT(*) FROM categoria WHERE estado = 1")->fetchColumn();
$totalMov = $pdo->query("SELECT COUNT(*) FROM movimiento")->fetchColumn();

// Productos con stock bajo
$stmtBajo = $pdo->query("SELECT * FROM producto WHERE stock_actual <= stock_minimo AND estado = 1");
$productosBajo = $stmtBajo->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="p-4 mb-4 bg-white rounded-3 shadow-sm border">
    <h1 class="display-6 fw-bold text-primary">Bienvenido, <?= htmlspecialchars($_SESSION['nombre_usuario']) ?> 👋</h1>
    <p class="lead mb-0">Panel de control general de TecnoStock.</p>
</div>

<!-- Tarjetas de resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title text-uppercase mb-1">Productos Activos</h6>
                    <h2 class="mb-0 fw-bold"><?= $totalProd ?></h2>
                </div>
                <i class="bi bi-box-seam display-5"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title text-uppercase mb-1">Categorías</h6>
                    <h2 class="mb-0 fw-bold"><?= $totalCat ?></h2>
                </div>
                <i class="bi bi-tags display-5"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title text-uppercase mb-1">Movimientos Registrados</h6>
                    <h2 class="mb-0 fw-bold"><?= $totalMov ?></h2>
                </div>
                <i class="bi bi-arrow-left-right display-5"></i>
            </div>
        </div>
    </div>
</div>

<!-- Alertas de Stock Bajo -->
<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark fw-bold">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Productos con Stock Bajo / Crítico
    </div>
    <div class="card-body">
        <?php if (empty($productosBajo)): ?>
            <p class="text-muted mb-0">No hay productos con alertas de stock en este momento.</p>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($productosBajo as $prod): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong><?= htmlspecialchars($prod['nombre']) ?></strong> (Código: <?= htmlspecialchars($prod['codigo']) ?>)</span>
                        <span class="badge bg-danger rounded-pill">Stock: <?= $prod['stock_actual'] ?> / Mín: <?= $prod['stock_minimo'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>