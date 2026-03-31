<div class="login-card">
    <h2><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</h2>
    <p class="subtitle">Acceso al Sistema del Taller</p>

    <form method="POST" action="login.php?accion=login">
        <div class="mb-3">
            <label>Correo Electrónico:</label>
            <input type="email" name="correo" placeholder="ejemplo@correo.com" required>
        </div>

        <div class="mb-3">
            <label>Contraseña:</label>
            <input type="password" name="contrasena" placeholder="********" required>
        </div>

        <button type="submit" name="enviar">Entrar al Sistema</button>
    </form>
</div>
