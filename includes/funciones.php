<?php
require_once dirname(__FILE__) . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/', '', isset($_SERVER['HTTPS']), true);
    session_start();
}

/** Escapa texto para HTML. */
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function redirigir($url)
{
    header('Location: ' . $url);
    exit;
}

/* ---------- Autenticación ---------- */

function usuario_actual()
{
    return isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
}

function requiere_login()
{
    if (usuario_actual() === null) {
        redirigir('login.php');
    }
}

/**
 * Valida usuario y contraseña. Devuelve el usuario (sin hash) o null.
 */
function intentar_login($usuario, $password)
{
    $stmt = db()->prepare('SELECT id, usuario, password_hash, nombre FROM usuarios WHERE usuario = ?');
    $stmt->execute(array($usuario));
    $fila = $stmt->fetch();

    if (!$fila || !password_verify($password, $fila['password_hash'])) {
        return null;
    }

    session_regenerate_id(true);
    unset($fila['password_hash']);
    $_SESSION['usuario'] = $fila;
    return $fila;
}

/* ---------- CSRF ---------- */

function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = function_exists('random_bytes')
            ? bin2hex(random_bytes(32))
            : bin2hex(openssl_random_pseudo_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo()
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verificar_csrf()
{
    $token = isset($_POST['csrf']) ? $_POST['csrf'] : '';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        exit('Solicitud inválida. Volvé atrás y recargá la página.');
    }
}

/* ---------- Mensajes flash ---------- */

function flash($tipo, $texto)
{
    $_SESSION['flash'] = array('tipo' => $tipo, 'texto' => $texto);
}

function mostrar_flash()
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alert alert-' . e($f['tipo']) . ' alert-dismissible fade show" role="alert">' . e($f['texto'])
         . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>';
}

function post($campo)
{
    return isset($_POST[$campo]) ? trim($_POST[$campo]) : '';
}
