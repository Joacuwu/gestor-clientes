<?php
/**
 * Alta (sin ?id) y edición (con ?id) de clientes.
 */
require_once dirname(__FILE__) . '/includes/funciones.php';
require_once dirname(__FILE__) . '/includes/clientes.php';
require_once dirname(__FILE__) . '/includes/layout.php';
requiere_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$campos = array('razon_social', 'cuit', 'email', 'telefono', 'ciudad', 'estado', 'notas');
$datos = array_fill_keys($campos, '');
$datos['estado'] = 'activo';
$errores = array();

if ($id) {
    $existente = cliente_buscar($id);
    if (!$existente) {
        flash('warning', 'El cliente no existe.');
        redirigir('index.php');
    }
    foreach ($campos as $c) {
        $datos[$c] = (string) $existente[$c];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    foreach ($campos as $c) {
        $datos[$c] = post($c);
    }
    $errores = cliente_validar($datos, $id);

    if (!$errores) {
        $u = usuario_actual();
        cliente_guardar($datos, $id, $u['id']);
        flash('success', $id ? 'Cliente actualizado.' : 'Cliente creado.');
        redirigir('index.php');
    }
}

function campo_error($errores, $campo)
{
    return isset($errores[$campo]) ? '<div class="invalid-feedback">' . e($errores[$campo]) . '</div>' : '';
}

function clase_error($errores, $campo)
{
    return isset($errores[$campo]) ? ' is-invalid' : '';
}

layout_inicio($id ? 'Editar cliente' : 'Nuevo cliente');
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Clientes</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo $id ? 'Editar' : 'Nuevo'; ?></li>
    </ol>
</nav>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="h4 mb-4"><?php echo $id ? 'Editar cliente' : 'Nuevo cliente'; ?></h1>
        <form method="post" novalidate id="form-cliente">
            <?php echo csrf_campo(); ?>
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="razon_social" class="form-label">Razón social *</label>
                    <input type="text" class="form-control<?php echo clase_error($errores, 'razon_social'); ?>" id="razon_social" name="razon_social"
                           value="<?php echo e($datos['razon_social']); ?>" maxlength="150" required>
                    <?php echo campo_error($errores, 'razon_social'); ?>
                </div>
                <div class="col-md-4">
                    <label for="cuit" class="form-label">CUIT</label>
                    <input type="text" class="form-control<?php echo clase_error($errores, 'cuit'); ?>" id="cuit" name="cuit"
                           value="<?php echo e($datos['cuit']); ?>" maxlength="13" placeholder="20-12345678-9">
                    <?php echo campo_error($errores, 'cuit'); ?>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control<?php echo clase_error($errores, 'email'); ?>" id="email" name="email"
                           value="<?php echo e($datos['email']); ?>" maxlength="150">
                    <?php echo campo_error($errores, 'email'); ?>
                </div>
                <div class="col-md-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control<?php echo clase_error($errores, 'telefono'); ?>" id="telefono" name="telefono"
                           value="<?php echo e($datos['telefono']); ?>" maxlength="30">
                    <?php echo campo_error($errores, 'telefono'); ?>
                </div>
                <div class="col-md-3">
                    <label for="ciudad" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad" value="<?php echo e($datos['ciudad']); ?>" maxlength="80">
                </div>
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select<?php echo clase_error($errores, 'estado'); ?>" id="estado" name="estado">
                        <option value="activo" <?php echo $datos['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $datos['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                    <?php echo campo_error($errores, 'estado'); ?>
                </div>
                <div class="col-12">
                    <label for="notas" class="form-label">Notas</label>
                    <textarea class="form-control" id="notas" name="notas" rows="3"><?php echo e($datos['notas']); ?></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Guardar</button>
                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php layout_fin(); ?>
