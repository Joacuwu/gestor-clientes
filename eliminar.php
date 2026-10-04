<?php
require_once dirname(__FILE__) . '/includes/funciones.php';
require_once dirname(__FILE__) . '/includes/clientes.php';
requiere_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}
verificar_csrf();

if (cliente_eliminar(isset($_POST['id']) ? $_POST['id'] : 0)) {
    flash('success', 'Cliente eliminado.');
} else {
    flash('warning', 'El cliente no existe o ya fue eliminado.');
}
redirigir('index.php');
