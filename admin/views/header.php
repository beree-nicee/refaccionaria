<?php
require_once(__DIR__."/../sistema.class.php");
$_sys = new Sistema();
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Taller - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; }
        .navbar-brand { font-weight: 600; letter-spacing: .5px; }
        .nav-link.active { font-weight: 500; }
        .badge-rol { font-size: .7rem; vertical-align: middle; }
    </style>
  </head>
  <body>
    <header>
        <img src="../images/banner.png" alt="Banner Taller" class="w-100" style="max-height:120px;object-fit:cover;">
        <nav class="navbar navbar-expand-lg bg-dark navbar-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-wrench"></i> Taller
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarMain">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                        <!-- Catálogos principales -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-boxes-stacked"></i> Catálogos
                            </a>
                            <ul class="dropdown-menu">
                                <?php if($_sys->verificarPermiso('refaccion_leer')): ?>
                                <li><a class="dropdown-item" href="refaccion.php"><i class="fas fa-cog fa-fw"></i> Refacciones</a></li>
                                <?php endif; ?>
                                <?php if($_sys->verificarPermiso('categoria_leer')): ?>
                                <li><a class="dropdown-item" href="categoria_refaccion.php"><i class="fas fa-tags fa-fw"></i> Categorías</a></li>
                                <?php endif; ?>
                                <?php if($_sys->verificarPermiso('servicio_leer')): ?>
                                <li><a class="dropdown-item" href="servicio.php"><i class="fas fa-tools fa-fw"></i> Servicios</a></li>
                                <?php endif; ?>
                                <?php if($_sys->verificarPermiso('paquete_leer')): ?>
                                <li><a class="dropdown-item" href="paquete.php"><i class="fas fa-box fa-fw"></i> Paquetes</a></li>
                                <?php endif; ?>
                                <?php if($_sys->verificarPermiso('compatibilidad_leer')): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="compatibilidad.php"><i class="fas fa-car fa-fw"></i> Compatibilidad</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- Personas -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-users"></i> Personas
                            </a>
                            <ul class="dropdown-menu">
                                <?php if($_sys->verificarPermiso('usuario_leer')): ?>
                                <li><a class="dropdown-item" href="usuario.php"><i class="fas fa-user fa-fw"></i> Usuarios</a></li>
                                <?php endif; ?>
                                <?php if($_sys->verificarPermiso('cliente_leer')): ?>
                                <li><a class="dropdown-item" href="cliente.php"><i class="fas fa-user-tie fa-fw"></i> Clientes</a></li>
                                <?php endif; ?>
                                <?php if($_sys->verificarPermiso('empleado_leer')): ?>
                                <li><a class="dropdown-item" href="empleado.php"><i class="fas fa-id-badge fa-fw"></i> Empleados</a></li>
                                <?php endif; ?>
                                <?php if($_sys->verificarPermiso('vehiculo_leer')): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="vehiculo.php"><i class="fas fa-car-side fa-fw"></i> Vehículos</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- Operaciones -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-calendar-check"></i> Operaciones
                            </a>
                            <ul class="dropdown-menu">
                                <?php if($_sys->verificarPermiso('cita_leer')): ?>
                                <li><a class="dropdown-item" href="cita.php"><i class="fas fa-calendar-alt fa-fw"></i> Citas</a></li>
                                <?php endif; ?>
                                <?php if($_sys->verificarPermiso('orden_leer')): ?>
                                <li><a class="dropdown-item" href="orden.php"><i class="fas fa-file-invoice fa-fw"></i> Órdenes de compra</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- Seguridad (solo admin) -->
                        <?php if($_sys->esAdmin()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-shield-alt"></i> Seguridad
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="rol.php"><i class="fas fa-user-tag fa-fw"></i> Roles</a></li>
                                <li><a class="dropdown-item" href="permiso.php"><i class="fas fa-key fa-fw"></i> Permisos</a></li>
                                <li><a class="dropdown-item" href="rol_permiso.php"><i class="fas fa-shield-alt fa-fw"></i> Rol - Permisos</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                    </ul>

                    <!-- Usuario en sesión -->
                    <ul class="navbar-nav ms-auto">
                        <?php if(isset($_SESSION['id_usuario'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-circle-user"></i>
                                <?= htmlspecialchars(($_SESSION['nombre'] ?: $_SESSION['email'])) ?>
                                <span class="badge bg-secondary badge-rol">
                                    <?= htmlspecialchars($_SESSION['rol'] ?? '') ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item text-danger" href="login.php?accion=logout">
                                    <i class="fas fa-right-from-bracket fa-fw"></i> Cerrar sesión
                                </a></li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-right-to-bracket"></i> Iniciar sesión</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <div class="container-fluid px-0">
