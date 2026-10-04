<?php
require_once dirname(__FILE__) . '/includes/funciones.php';
require_once dirname(__FILE__) . '/includes/clientes.php';
require_once dirname(__FILE__) . '/includes/layout.php';
requiere_login();

$q      = isset($_GET['q']) ? trim($_GET['q']) : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$pagina = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;

$resultado = clientes_listar($q, $estado, $pagina, POR_PAGINA);
$paginas = max(1, (int) ceil($resultado['total'] / POR_PAGINA));

function url_pagina($n, $q, $estado)
{
    return 'index.php?' . http_build_query(array('q' => $q, 'estado' => $estado, 'p' => $n));
}

layout_inicio('Clientes');
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 m-0">Clientes <span class="badge text-bg-secondary align-middle fs-6"><?php echo $resultado['total']; ?></span></h1>
    <a href="cliente.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo cliente</a>
</div>

<form class="row g-2 mb-3" method="get" role="search">
    <div class="col-md-7">
        <input type="search" name="q" class="form-control" placeholder="Buscar por razón social, CUIT, email o ciudad" value="<?php echo e($q); ?>">
    </div>
    <div class="col-8 col-md-3">
        <select name="estado" class="form-select" aria-label="Estado">
            <option value="">Todos los estados</option>
            <option value="activo" <?php echo $estado === 'activo' ? 'selected' : ''; ?>>Activos</option>
            <option value="inactivo" <?php echo $estado === 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
        </select>
    </div>
    <div class="col-4 col-md-2 d-grid">
        <button class="btn btn-outline-primary"><i class="bi bi-search"></i> Buscar</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Razón social</th>
                    <th class="d-none d-md-table-cell">CUIT</th>
                    <th class="d-none d-lg-table-cell">Email</th>
                    <th class="d-none d-lg-table-cell">Teléfono</th>
                    <th class="d-none d-sm-table-cell">Ciudad</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$resultado['filas']): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No se encontraron clientes.</td></tr>
            <?php endif; ?>
            <?php foreach ($resultado['filas'] as $c): ?>
                <tr>
                    <td class="fw-semibold"><?php echo e($c['razon_social']); ?></td>
                    <td class="d-none d-md-table-cell text-nowrap"><?php echo e($c['cuit']); ?></td>
                    <td class="d-none d-lg-table-cell"><?php echo e($c['email']); ?></td>
                    <td class="d-none d-lg-table-cell text-nowrap"><?php echo e($c['telefono']); ?></td>
                    <td class="d-none d-sm-table-cell"><?php echo e($c['ciudad']); ?></td>
                    <td>
                        <span class="badge <?php echo $c['estado'] === 'activo' ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                            <?php echo e(ucfirst($c['estado'])); ?>
                        </span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="cliente.php?id=<?php echo (int) $c['id']; ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" title="Eliminar"
                                data-id="<?php echo (int) $c['id']; ?>" data-nombre="<?php echo e($c['razon_social']); ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($paginas > 1): ?>
<nav class="mt-3" aria-label="Paginación">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo e(url_pagina($pagina - 1, $q, $estado)); ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $paginas; $i++): ?>
        <li class="page-item <?php echo $i === $pagina ? 'active' : ''; ?>">
            <a class="page-link" href="<?php echo e(url_pagina($i, $q, $estado)); ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?php echo $pagina >= $paginas ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo e(url_pagina($pagina + 1, $q, $estado)); ?>">Siguiente</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Confirmación de borrado -->
<div class="modal fade" id="modal-eliminar" tabindex="-1" aria-labelledby="titulo-eliminar" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" action="eliminar.php">
            <?php echo csrf_campo(); ?>
            <input type="hidden" name="id" id="eliminar-id">
            <div class="modal-header">
                <h2 class="modal-title h5" id="titulo-eliminar">Eliminar cliente</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">¿Seguro que querés eliminar a <strong id="eliminar-nombre"></strong>? Esta acción no se puede deshacer.</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger"><i class="bi bi-trash"></i> Eliminar</button>
            </div>
        </form>
    </div>
</div>
<?php layout_fin(); ?>
