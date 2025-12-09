<?php
$token = $_GET['token'];
?>

<form action="/process/reset_process.php" method="POST">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

    <div class="form-floating mb-3">
        <input class="form-control" name="password" type="password" required />
        <label>Nueva Contraseña</label>
    </div>

    <button class="btn btn-primary w-100" type="submit">Restablecer</button>
</form>