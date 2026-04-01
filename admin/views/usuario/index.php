<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-users"></i> Usuarios</h2>
        <?php if ($app->esAdmin()): ?>
        <a href="usuario.php?accion=crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Usuario
        </a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width:50px">Foto</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th class="text-center">Rol</th>
                        <th class="text-center">Estado</th>
                        <th>Registro</th>
                        <th class="text-end">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            No hay usuarios registrados
                        </td>
                    </tr>
                <?php else: foreach ($usuarios as $u): ?>
                    <?php
                    $fotoSrc = !empty($u['fotografia'])
                        ? "../uploads/" . $u['carpeta_foto'] . "/" . htmlspecialchars($u['fotografia'])
                        : "../images/default-avatar.jpg";
                    $rolColores = [
                        'Administrador' => 'danger',
                        'Tecnico'       => 'warning',
                        'Cliente'       => 'primary',
                    ];
                    $estColores = [
                        'activa'     => 'success',
                        'suspendida' => 'warning',
                        'eliminada'  => 'danger',
                    ];
                    ?>
                    <tr>
                        <td>
                            <img src="<?= $fotoSrc ?>"
                                 alt="avatar"
                                 class="rounded-circle border"
                                 style="width:38px;height:38px;object-fit:cover"
                                 onerror="this.src='../images/default-avatar.jpg'">
                        </td>
                        <td>
                            <strong>
                                <?= htmlspecialchars(trim($u['nombre'] . ' ' . $u['apellidos'])) ?: '—' ?>
                            </strong>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['telefono'] ?: '—') ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $rolColores[$u['nombre_rol']] ?? 'secondary' ?>">
                                <?= htmlspecialchars($u['nombre_rol']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $estColores[$u['estado_cuenta']] ?? 'secondary' ?>">
                                <?= ucfirst($u['estado_cuenta']) ?>
                            </span>
                        </td>
                        <td>
                            <small><?= $app->formatearFecha($u['fecha_registro']) ?></small>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="usuario.php?accion=actualizar&id=<?= $u['id_usuario'] ?>"
                                   class="btn btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($app->esAdmin()): ?>
                                <a href="usuario.php?accion=borrar&id=<?= $u['id_usuario'] ?>"
                                   class="btn btn-danger" title="Eliminar"
                                   onclick="return confirm('¿Eliminar usuario?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>