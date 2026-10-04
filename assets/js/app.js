/* Gestor de clientes: interacciones con jQuery */
$(function () {
    'use strict';

    // Modal de confirmación de borrado
    var modalEl = document.getElementById('modal-eliminar');
    if (modalEl) {
        var modal = new bootstrap.Modal(modalEl);
        $('.btn-eliminar').on('click', function () {
            $('#eliminar-id').val($(this).data('id'));
            $('#eliminar-nombre').text($(this).data('nombre'));
            modal.show();
        });
    }

    // Formato automático del CUIT: 20123456789 -> 20-12345678-9
    $('#cuit').on('input', function () {
        var n = $(this).val().replace(/\D/g, '').slice(0, 11);
        if (n.length > 10) {
            n = n.slice(0, 2) + '-' + n.slice(2, 10) + '-' + n.slice(10);
        } else if (n.length > 2) {
            n = n.slice(0, 2) + '-' + n.slice(2);
        }
        $(this).val(n);
    });

    // Las alertas de éxito se cierran solas
    setTimeout(function () { $('.alert-success').fadeOut(400); }, 4000);
});
