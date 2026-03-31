<?php
require_once(__DIR__."/sistema.class.php");
$app = new Sistema();
$app->conectar();
$app->requiereLogin();

// 1. KPI: INGRESOS TOTALES (Ventas completadas)
$sqlVentas = "SELECT SUM(total_general) as total FROM Orden_Compra WHERE estado_orden = 'completada'";
$stmtV = $app->db->prepare($sqlVentas);
$stmtV->execute();
$totalVentas = $stmtV->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 2. KPI: PRODUCTIVIDAD (Servicios terminados hoy)
$hoy = date('Y-m-d');
$sqlProd = "SELECT COUNT(*) as total FROM Cita WHERE estado_cita = 'completada' AND fecha_cita = :hoy";
$stmtP = $app->db->prepare($sqlProd);
$stmtP->execute([':hoy' => $hoy]);
$serviciosHoy = $stmtP->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 3. KPI: PRÓXIMAS CITAS (Citas pendientes o confirmadas para hoy y mañana)
$sqlCitas = "SELECT COUNT(*) as total FROM Cita WHERE estado_cita IN ('pendiente', 'confirmada') AND fecha_cita >= :hoy";
$stmtC = $app->db->prepare($sqlCitas);
$stmtC->execute([':hoy' => $hoy]);
$proximasCitas = $stmtC->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 4. KPI: STOCK BAJO (Refacciones con menos de 5 unidades)
$sqlStock = "SELECT COUNT(*) as total FROM Refaccion WHERE stock_actual <= 5";
$stmtS = $app->db->prepare($sqlStock);
$stmtS->execute();
$stockCritico = $stmtS->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

include_once(__DIR__."/views/header.php");
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="display-6"><i class="fas fa-chart-pie text-primary"></i> Panel de Control Taller</h2>
            <p class="text-muted">Resumen de actividad al día <?= date('d/m/Y') ?></p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-50 small">Ingresos Totales</div>
                            <h3 class="fw-bold mb-0">$<?= number_format($totalVentas, 2) ?></h3>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-50 small">Productividad Hoy</div>
                            <h3 class="fw-bold mb-0"><?= $serviciosHoy ?> <small class="fs-6">servicios</small></h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-50 small">Próximas Citas</div>
                            <h3 class="fw-bold mb-0"><?= $proximasCitas ?> <small class="fs-6">en agenda</small></h3>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm <?= $stockCritico > 0 ? 'bg-danger' : 'bg-secondary' ?> text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-50 small">Alertas de Stock</div>
                            <h3 class="fw-bold mb-0"><?= $stockCritico ?> <small class="fs-6">críticos</small></h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12 text-center text-muted">
            <p><small><i class="fas fa-info-circle"></i> Los datos se actualizan automáticamente al concretar órdenes o cerrar citas.</small></p>
        </div>
    </div>
</div>

<?php include_once(__DIR__."/views/footer.php"); ?>