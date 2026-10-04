<?php
/**
 * Acceso a datos de clientes. Todas las consultas son preparadas.
 */
require_once dirname(__FILE__) . '/db.php';

/**
 * Lista paginada con búsqueda por razón social, CUIT, email o ciudad y filtro por estado.
 * Devuelve array('filas' => [...], 'total' => int).
 */
function clientes_listar($busqueda, $estado, $pagina, $porPagina)
{
    $where = array();
    $params = array();

    if ($busqueda !== '') {
        // Con PREPARES nativos (MySQL) cada marcador se usa una sola vez
        $where[] = '(razon_social LIKE :q1 OR cuit LIKE :q2 OR email LIKE :q3 OR ciudad LIKE :q4)';
        $params[':q1'] = $params[':q2'] = $params[':q3'] = $params[':q4'] = '%' . $busqueda . '%';
    }
    if ($estado === 'activo' || $estado === 'inactivo') {
        $where[] = 'estado = :estado';
        $params[':estado'] = $estado;
    }
    $sqlWhere = count($where) ? ' WHERE ' . implode(' AND ', $where) : '';

    $stmt = db()->prepare('SELECT COUNT(*) FROM clientes' . $sqlWhere);
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();

    $offset = max(0, ($pagina - 1) * $porPagina);
    $stmt = db()->prepare('SELECT * FROM clientes' . $sqlWhere . ' ORDER BY razon_social ASC LIMIT :limite OFFSET :offset');
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limite', (int) $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
    $stmt->execute();

    return array('filas' => $stmt->fetchAll(), 'total' => $total);
}

function cliente_buscar($id)
{
    $stmt = db()->prepare('SELECT * FROM clientes WHERE id = ?');
    $stmt->execute(array((int) $id));
    $fila = $stmt->fetch();
    return $fila ? $fila : null;
}

/**
 * Valida los datos del formulario. Devuelve array campo => mensaje (vacío si está todo bien).
 */
function cliente_validar(array $d, $id = null)
{
    $errores = array();

    if (mb_strlen($d['razon_social'], 'UTF-8') < 2 || mb_strlen($d['razon_social'], 'UTF-8') > 150) {
        $errores['razon_social'] = 'La razón social es obligatoria (2 a 150 caracteres).';
    }
    if ($d['cuit'] !== '') {
        if (!cuit_valido($d['cuit'])) {
            $errores['cuit'] = 'El CUIT no es válido (formato 20-12345678-9).';
        } else {
            $stmt = db()->prepare('SELECT id FROM clientes WHERE cuit = ? AND id <> ?');
            $stmt->execute(array($d['cuit'], (int) $id));
            if ($stmt->fetch()) {
                $errores['cuit'] = 'Ya existe un cliente con ese CUIT.';
            }
        }
    }
    if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El email no es válido.';
    }
    if ($d['telefono'] !== '' && !preg_match('/^[0-9 +()\-]{6,30}$/', $d['telefono'])) {
        $errores['telefono'] = 'El teléfono solo puede tener números, espacios y + ( ) -.';
    }
    if (!in_array($d['estado'], array('activo', 'inactivo'), true)) {
        $errores['estado'] = 'Estado inválido.';
    }

    return $errores;
}

/**
 * Valida el dígito verificador de un CUIT/CUIL argentino.
 */
function cuit_valido($cuit)
{
    $numeros = preg_replace('/[^0-9]/', '', $cuit);
    if (strlen($numeros) !== 11) {
        return false;
    }
    $pesos = array(5, 4, 3, 2, 7, 6, 5, 4, 3, 2);
    $suma = 0;
    for ($i = 0; $i < 10; $i++) {
        $suma += (int) $numeros[$i] * $pesos[$i];
    }
    $verificador = 11 - ($suma % 11);
    if ($verificador === 11) {
        $verificador = 0;
    } elseif ($verificador === 10) {
        $verificador = 9;
    }
    return $verificador === (int) $numeros[10];
}

function cliente_guardar(array $d, $id, $usuarioId)
{
    $ahora = date('Y-m-d H:i:s');
    $valores = array(
        ':razon_social' => $d['razon_social'],
        ':cuit'         => $d['cuit'] !== '' ? $d['cuit'] : null,
        ':email'        => $d['email'] !== '' ? $d['email'] : null,
        ':telefono'     => $d['telefono'] !== '' ? $d['telefono'] : null,
        ':ciudad'       => $d['ciudad'] !== '' ? $d['ciudad'] : null,
        ':estado'       => $d['estado'],
        ':notas'        => $d['notas'] !== '' ? $d['notas'] : null,
    );

    if ($id) {
        $valores[':id'] = (int) $id;
        $valores[':ahora'] = $ahora;
        $stmt = db()->prepare('UPDATE clientes SET razon_social = :razon_social, cuit = :cuit, email = :email,
                               telefono = :telefono, ciudad = :ciudad, estado = :estado, notas = :notas,
                               actualizado_en = :ahora WHERE id = :id');
        $stmt->execute($valores);
        return (int) $id;
    }

    $valores[':creado_por'] = (int) $usuarioId;
    $valores[':ahora'] = $ahora;
    $stmt = db()->prepare('INSERT INTO clientes (razon_social, cuit, email, telefono, ciudad, estado, notas, creado_por, creado_en)
                           VALUES (:razon_social, :cuit, :email, :telefono, :ciudad, :estado, :notas, :creado_por, :ahora)');
    $stmt->execute($valores);
    return (int) db()->lastInsertId();
}

function cliente_eliminar($id)
{
    $stmt = db()->prepare('DELETE FROM clientes WHERE id = ?');
    $stmt->execute(array((int) $id));
    return $stmt->rowCount() > 0;
}
