$(document).ready(function () {

    $(document).on("click", ".speiseplan-artikel-btn", function (btn) {
        btn = $(btn.currentTarget);
        var artikelID = btn.attr("artikelid");
        var speiseplanID = btn.attr("speiseplanid");

        $.ajax({
            type: "POST",
            url: "./modules/AjaxController.php",
            data: {
                class: "KundenBestellen",
                method: "getArtikelModal",
                params: {
                    id: artikelID,
                    id2: speiseplanID
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
 * Bucht einen Artikel
 * @param speiseplanID
 */
function buchen(speiseplanID) {
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "KundenBestellen",
            method: "artikelBuchen",
            params: {
                id: speiseplanID
            }
        },
        success: function (success) {
            if (success[0] == 1) {
                $.amaran({
                    'message': 'Artikel gebucht!',
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
                reloadKundenBestellenSpeisesplan();
            } else {
                $.amaran({
                    'message': success.substring(1),
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
            }
            $('#artikelModal').modal('hide');
        }
    });
}

/**
 * Storniert einen Artikel
 * @param speiseplanID
 */
function stornieren(speiseplanID) {
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "KundenBestellen",
            method: "artikelStornieren",
            params: {
                id: speiseplanID
            }
        },
        success: function (success) {
            if (success == 1) {
                $.amaran({
                    'message': 'Artikel storniert!',
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
                reloadKundenBestellenSpeisesplan();
            } else {
                $.amaran({
                    'message': 'Artikel konnte nicht storniert werden! Error: ' + success,
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
            }
            $('#artikelModal').modal('hide');
        }
    });
}

/**
 * Liked einen Artikel
 * @param speiseplanID
 */
function liken(speiseplanID) {
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "KundenBestellen",
            method: "artikelLiken",
            params: {
                id: speiseplanID
            }
        },
        success: function (success) {
            if (success[0] == 1) {
                $.amaran({
                    'message': 'Artikel wurde geliked!',
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
                reloadKundenBestellenSpeisesplan();
            } else {
                $.amaran({
                    'message': success.substr(1),
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
            }
            $('#artikelModal').modal('hide');
        }
    });
}

/**
 * Aktualisiert den Kunden-Bestell-Speiseplan mit Daten vom Server
 */
function reloadKundenBestellenSpeisesplan() {
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "KundenBestellen",
            method: "getSpeiseplanTableBody",
            params: {
                //kw: "5",
                kw: findGetParameter("kw"),
                jahr: findGetParameter("jahr")
            }
        },
        success: function (bestellenSpeiseplanHTML) {
            $("#speiseplanTableBody").html(bestellenSpeiseplanHTML);
        }
    });
}

/**
 * Abrufen der per Übergabe gesuchten GET-Parameter
 * @param parameterName
 * @returns {*}
 */
function findGetParameter(parameterName) {
    var result = null,
        tmp = [];
    var items = location.search.substr(1).split("&");
    for (var index = 0; index < items.length; index++) {
        tmp = items[index].split("=");
        if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
    }
    return result;
}




