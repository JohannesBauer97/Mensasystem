$(document).ready(function () {

    /**
     * Öffnet das Modal für einen Artikel
     */
    $(document).on("click", ".speiseplan-artikel-btn", function (btn) {
        btn = $(btn.currentTarget);
        var artikelID = btn.attr("artikelid");

        $.ajax({
            type: "POST",
            url: "./modules/AjaxController.php",
            data: {
                class: "Home",
                method: "getArtikelModal",
                params: {
                    id: artikelID
                }
            },
            success: function (htmlCode) {
                $("#ModalPlaceholder").html(htmlCode);
                $("#artikelModal").modal('show');
            }
        });


    });

});

/**
 * Öffnet das Modal für einen Artikel
 * @param artikelID
 */
function openArtikelModal(artikelID) {
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "Home",
            method: "getArtikelModal",
            params: {
                id: artikelID
            }
        },
        success: function (htmlCode) {
            $("#ModalPlaceholder").html(htmlCode);
            $("#artikelModal").modal('show');
        }
    });
}