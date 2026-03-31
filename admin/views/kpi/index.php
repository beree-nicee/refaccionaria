<?php
// Preparar datos para JS
$meses    = array_column($ingresosMes, 'mes');
$ingresos = array_column($ingresosMes, 'total');

$estadosOrden  = array_column($ordenesPorEstado, 'estado_orden');
$totalesOrden  = array_column($ordenesPorEstado, 'total');

$nombresServ   = array_column($serviciosTop, 'nombre_servicio');
$totalesServ   = array_column($serviciosTop, 'total');

$nombresRef    = array_column($refaccionesTop, 'nombre');
$totalesRef    = array_column($refaccionesTop, 'total_vendido');

$estadosCita   = array_column($citasPorEstado, 'estado_cita');
$totalesCita   = array_column($citasPorEstado, 'total');

// Formatear meses para mostrar
$mesesLabel = array_map(function($m) {
    $partes = explode('-', $m);
    $meses = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr',
              '05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago',
              '09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
    return ($meses[$partes[1]] ?? $partes[1]) . ' ' . $partes[0];
}, $meses);
?>

<div class="container-fluid mt-4 px-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <i class="fas fa-chart-line fa-lg text-primary"></i>
        <h2 class="mb-0">Dashboard / KPIs</h2>
        <small class="text-muted ms-2">Actualizado: <?= date('d/m/Y H:i') ?></small>
    </div>

    <!-- ── Tarjetas resumen ── -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body py-3">
                    <i class="fas fa-file-invoice fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0 fw-bold"><?= number_format($resumen['total_ordenes']) ?></h3>
                    <small class="text-muted">Órdenes totales</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body py-3">
                    <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                    <h3 class="mb-0 fw-bold">$<?= number_format($resumen['ingresos_totales'], 0) ?></h3>
                    <small class="text-muted">Ingresos completados</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body py-3">
                    <i class="fas fa-users fa-2x text-info mb-2"></i>
                    <h3 class="mb-0 fw-bold"><?= number_format($resumen['total_clientes']) ?></h3>
                    <small class="text-muted">Clientes</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body py-3">
                    <i class="fas fa-calendar-check fa-2x text-warning mb-2"></i>
                    <h3 class="mb-0 fw-bold"><?= number_format($resumen['total_citas']) ?></h3>
                    <small class="text-muted">Citas activas</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 text-center <?= $resumen['stock_bajo'] > 0 ? 'border-danger' : '' ?>">
                <div class="card-body py-3">
                    <i class="fas fa-boxes fa-2x <?= $resumen['stock_bajo'] > 0 ? 'text-danger' : 'text-secondary' ?> mb-2"></i>
                    <h3 class="mb-0 fw-bold <?= $resumen['stock_bajo'] > 0 ? 'text-danger' : '' ?>">
                        <?= number_format($resumen['stock_bajo']) ?>
                    </h3>
                    <small class="text-muted">Stock bajo</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Gráficas fila 1 ── -->
    <div class="row g-3 mb-3">
        <!-- Ingresos por mes (línea) -->
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0 pt-3">
                    <i class="fas fa-chart-line text-primary"></i> Ingresos últimos 6 meses
                </div>
                <div class="card-body">
                    <canvas id="chartIngresos" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Estado de órdenes (dona) -->
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0 pt-3">
                    <i class="fas fa-chart-pie text-warning"></i> Estado de órdenes
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="chartOrdenes" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Gráficas fila 2 ── -->
    <div class="row g-3 mb-4">
        <!-- Servicios más solicitados (barras horizontal) -->
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0 pt-3">
                    <i class="fas fa-tools text-info"></i> Servicios más solicitados
                </div>
                <div class="card-body">
                    <canvas id="chartServicios" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- Refacciones más vendidas (barras) -->
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0 pt-3">
                    <i class="fas fa-cog text-success"></i> Refacciones más vendidas
                </div>
                <div class="card-body">
                    <canvas id="chartRefacciones" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- Estado de citas (dona) -->
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0 pt-3">
                    <i class="fas fa-calendar text-danger"></i> Estado de citas
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="chartCitas" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js desde CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Datos desde PHP
const meses       = <?= json_encode($mesesLabel) ?>;
const ingresos    = <?= json_encode(array_map('floatval', $ingresos)) ?>;
const estOrden    = <?= json_encode($estadosOrden) ?>;
const totOrden    = <?= json_encode(array_map('intval', $totalesOrden)) ?>;
const nomServ     = <?= json_encode($nombresServ) ?>;
const totServ     = <?= json_encode(array_map('intval', $totalesServ)) ?>;
const nomRef      = <?= json_encode($nombresRef) ?>;
const totRef      = <?= json_encode(array_map('intval', $totalesRef)) ?>;
const estCita     = <?= json_encode($estadosCita) ?>;
const totCita     = <?= json_encode(array_map('intval', $totalesCita)) ?>;

// Paleta de colores
const paleta = ['#0d6efd','#198754','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14','#20c997'];

const opts = { responsive: true, plugins: { legend: { position: 'bottom' } } };

// 1. Ingresos por mes
new Chart(document.getElementById('chartIngresos'), {
    type: 'line',
    data: {
        labels: meses,
        datasets: [{
            label: 'Ingresos ($)',
            data: ingresos,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,0.08)',
            borderWidth: 2,
            pointRadius: 4,
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        ...opts,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: v => '$' + v.toLocaleString() }
            }
        }
    }
});

// 2. Órdenes por estado
new Chart(document.getElementById('chartOrdenes'), {
    type: 'doughnut',
    data: {
        labels: estOrden.map(e => e.charAt(0).toUpperCase() + e.slice(1)),
        datasets: [{ data: totOrden, backgroundColor: paleta }]
    },
    options: { ...opts, cutout: '60%' }
});

// 3. Servicios más solicitados
new Chart(document.getElementById('chartServicios'), {
    type: 'bar',
    data: {
        labels: nomServ,
        datasets: [{
            label: 'Veces solicitado',
            data: totServ,
            backgroundColor: 'rgba(13,202,240,0.7)',
            borderColor: '#0dcaf0',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        indexAxis: 'y',
        ...opts,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// 4. Refacciones más vendidas
new Chart(document.getElementById('chartRefacciones'), {
    type: 'bar',
    data: {
        labels: nomRef,
        datasets: [{
            label: 'Unidades vendidas',
            data: totRef,
            backgroundColor: 'rgba(25,135,84,0.7)',
            borderColor: '#198754',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        ...opts,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// 5. Citas por estado
new Chart(document.getElementById('chartCitas'), {
    type: 'doughnut',
    data: {
        labels: estCita.map(e => e.replace('_',' ').replace(/\b\w/g, l => l.toUpperCase())),
        datasets: [{ data: totCita, backgroundColor: paleta }]
    },
    options: { ...opts, cutout: '60%' }
});
</script>
