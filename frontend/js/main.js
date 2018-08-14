$(function () {
    // Enables Bootstrap Tooltips everywhere!
    $('[data-toggle="tooltip"]').tooltip();
    $.extend(true, $.fn.dataTable.defaults, {
        "language": {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/German.json"
        }
    });
});