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

                        <!-- Catálogo / Inventario -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-store"></i> <?= $_sys->esCliente() ? 'Catálogo' : 'Inventario' ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="refaccion.php">
                                    <i class="fas fa-cog fa-fw"></i> Refacciones</a></li>
                                <li><a class="dropdown-item" href="servicio.php">
                                    <i class="fas fa-tools fa-fw"></i> Servicios</a></li>
                                <li><a class="dropdown-item" href="paquete.php">
                                    <i class="fas fa-box fa-fw"></i> Paquetes</a></li>
                                <?php if (!$_sys->esCliente()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="categoria_refaccion.php">
                                    <i class="fas fa-tags fa-fw"></i> Categorías</a></li>
                                <li><a class="dropdown-item" href="refaccion.php?accion=crear">
                                    <i class="fas fa-plus fa-fw"></i> Nueva refacción</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- Mis Datos / Usuarios -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?= $_sys->esCliente() ? 'Mis Datos' : 'Personas' ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="vehiculo.php">
                                    <i class="fas fa-car-side fa-fw"></i>
                                    <?= $_sys->esCliente() ? 'Mis vehículos' : 'Vehículos' ?></a></li>
                                <?php if (!$_sys->esCliente()): ?>
                                <li><a class="dropdown-item" href="cliente.php">
                                    <i class="fas fa-user-tie fa-fw"></i> Clientes</a></li>
                                <li><a class="dropdown-item" href="empleado.php">
                                    <i class="fas fa-id-badge fa-fw"></i> Empleados</a></li>
                                <?php if ($_sys->esAdmin()): ?>
                                <li><a class="dropdown-item" href="usuario.php">
                                    <i class="fas fa-users fa-fw"></i> Usuarios</a></li>
                                <?php endif; ?>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- Mis Citas y Compras / Operaciones -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-calendar-alt"></i>
                                <?= $_sys->esCliente() ? 'Mis Citas y Compras' : 'Operaciones' ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="cita.php">
                                    <i class="fas fa-clock fa-fw"></i>
                                    <?= $_sys->esCliente() ? 'Agendar / Mis citas' : 'Agenda general' ?></a></li>
                                <li><a class="dropdown-item" href="orden.php">
                                    <i class="fas fa-file-invoice-dollar fa-fw"></i>
                                    <?= $_sys->esCliente() ? 'Mis órdenes' : 'Órdenes de trabajo' ?></a></li>
                                <?php if ($_sys->esCliente()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="carrito.php">
                                    <i class="fas fa-shopping-cart fa-fw"></i> Mi carrito</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- KPIs (técnico y admin con permiso) -->
                        <?php if (!$_sys->esCliente() && $_sys->verificarPermiso('reporte_ver')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="kpi.php">
                                <i class="fas fa-chart-line"></i> KPIs
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Seguridad (solo admin) -->
                        <?php if ($_sys->esAdmin()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-shield-alt"></i> Seguridad
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="rol.php">
                                    <i class="fas fa-user-tag fa-fw"></i> Roles</a></li>
                                <li><a class="dropdown-item" href="permiso.php">
                                    <i class="fas fa-key fa-fw"></i> Permisos</a></li>
                                <li><a class="dropdown-item" href="rol_permiso.php">
                                    <i class="fas fa-shield-alt fa-fw"></i> Rol — Permisos</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                    </ul>

                    <!-- Usuario en sesión -->
                    <ul class="navbar-nav ms-auto align-items-center">

                        <!-- Badge carrito (solo cliente) -->
                        <?php if ($_sys->esCliente()): ?>
                        <li class="nav-item me-1">
                            <a class="nav-link position-relative" href="carrito.php" title="Mi carrito">
                                <i class="fas fa-shopping-cart"></i>
                                <?php
                                try {
                                    require_once(__DIR__.'/../models/carrito.php');
                                    $_ct = new Carrito();
                                    $_ni = (int)$_ct->contarItems();
                                    if ($_ni > 0):
                                ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                      style="font-size:.6rem"><?= $_ni ?></span>
                                <?php endif; } catch (Exception $ex) {} ?>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Dropdown de usuario -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?>
                                <span class="badge bg-info badge-rol">
                                    <?= htmlspecialchars($_SESSION['rol'] ?? '') ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <!-- Mi perfil: cliente → cliente.php, empleado → empleado.php -->
                                <li>
                                    <a class="dropdown-item" href="<?= $_sys->esCliente() ? 'cliente.php' : 'empleado.php' ?>">
                                        <i class="fas fa-user-edit fa-fw"></i> Mi perfil
                                    </a>
                                </li>
                                <?php if ($_sys->esCliente()): ?>
                                <li>
                                    <a class="dropdown-item" href="carrito.php">
                                        <i class="fas fa-shopping-cart fa-fw"></i> Mi carrito
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="cita.php">
                                        <i class="fas fa-calendar-check fa-fw"></i> Mis citas
                                    </a>
                                </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="login.php?accion=logout">
                                        <i class="fas fa-right-from-bracket fa-fw"></i> Cerrar sesión
                                    </a>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <div class="container-fluid px-3">
