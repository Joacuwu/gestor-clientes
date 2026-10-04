<?php
function layout_inicio($titulo)
{
    $u = usuario_actual();
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($titulo); ?> · <?php echo e(APP_NOMBRE); ?></title>
    <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
<?php if ($u): ?>
<nav class="navbar navbar-expand navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="bi bi-people-fill"></i> <?php echo e(APP_NOMBRE); ?></a>
        <div class="d-flex align-items-center gap-3 text-white">
            <span class="d-none d-sm-inline"><i class="bi bi-person-circle"></i> <?php echo e($u['nombre']); ?></span>
            <form action="logout.php" method="post" class="m-0">
                <?php echo csrf_campo(); ?>
                <button class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i> Salir</button>
            </form>
        </div>
    </div>
</nav>
<?php endif; ?>
<main class="container pb-5">
    <?php echo mostrar_flash(); ?>
<?php
}

function layout_fin()
{
    ?>
</main>
<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
<?php
}
