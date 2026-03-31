<?php
require_once(__DIR__."/../sistema.class.php");
$_sys = new Sistema();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Taller - <?= $_sys->esCliente() ? 'Portal Cliente' : 'Admin' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; }
        .navbar-brand { font-weight: 600; }
        .badge-rol { font-size: .7rem; vertical-align: middle; }
    </style>
</head>
<body>
    <header>
        <img src="../images/banner.png" alt="Banner" class="w-100" style="max-height:100px;object-fit:cover;">
        <nav class="navbar navbar-expand-lg bg-dark navbar-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php"><i class="fas fa-wrench"></i> Taller</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-store"></i> <?= $_sys->esCliente() ? 'Catálogo' : 'Inventario' ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="refaccion.php"><i class="fas fa-cog fa-fw"></i> Refacciones</a></li>
                                <li><a class="dropdown-item" href="servicio.php"><i class="fas fa-tools fa-fw"></i> Servicios</a></li>
                                <li><a class="dropdown-item" href="paquete.php"><i class="fas fa-box fa-fw"></i> Paquetes</a></li>
                                <?php if(!$_sys->esCliente()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="compatibilidad.php"><i class="fas fa-car fa-fw"></i> Compatibilidad</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?= $_sys->esCliente() ? 'Mis Datos' : 'Usuarios' ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="vehiculo.php"><i class="fas fa-car-side fa-fw"></i> <?= $_sys->esCliente() ? 'Mis Vehículos' : 'Vehículos Clientes' ?></a></li>
                                <?php if(!$_sys->esCliente()): ?>
                                    <li><a class="dropdown-item" href="cliente.php"><i class="fas fa-user-tie fa-fw"></i> Directorio Clientes</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-calendar-alt"></i> <?= $_sys->esCliente() ? 'Mis Citas y Compras' : 'Operaciones' ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="cita.php"><i class="fas fa-clock fa-fw"></i> <?= $_sys->esCliente() ? 'Agendar / Mis Citas' : 'Agenda General' ?></a></li>
                                <li><a class="dropdown-item" href="orden.php"><i class="fas fa-file-invoice-dollar fa-fw"></i> <?= $_sys->esCliente() ? 'Mis Órdenes' : 'Órdenes de Trabajo' ?></a></li>
                                <?php if($_sys->esCliente()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="carrito.php"><i class="fas fa-shopping-cart fa-fw"></i> Mi Carrito</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <?php if(!$_sys->esCliente()): ?>
                            <?php if($_sys->verificarPermiso('reporte_ver')): ?>
                                <li class="nav-item"><a class="nav-link" href="kpi.php"><i class="fas fa-chart-line"></i> KPIs</a></li>
                            <?php endif; ?>
                            <?php if($_sys->esAdmin()): ?>
                                <li class="nav-item"><a class="nav-link" href="rol.php"><i class="fas fa-shield-alt"></i> Seguridad</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?>
                                <span class="badge bg-info badge-rol"><?= $_SESSION['rol'] ?? '' ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item text-danger" href="login.php?accion=logout">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <div class="container mt-4">