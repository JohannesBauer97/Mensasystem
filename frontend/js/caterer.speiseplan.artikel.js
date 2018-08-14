$(document).ready(function () {
    /**
     * Datatable Plugin auf ArtikelTable initialisieren
     */
    $('#ArtikelTable').DataTable();

    /**
     * Event für BTN Click Abbrechen
     * Setzt alle Eingabefelder zurück
     */
    $("#artikel-abbrechen").click(function () {
        $("#title").val("");
        $("#description").val("");
        $("#preis").val("");
        $("#btn-dauerhaft").attr("checked", false);
        $("#lbl-dauerhaft").removeClass("active");
    });

    /**
     * Event bei BTN Click auf Artikel Anlegen
     * Fügt einen Artikel der Datenbank hinzu
     */
    $("#artikel-anlegen").click(function () {
        var title = $("#title").val();
        var descr = $("#description").val();
        var preis = $("#preis").val();
        var dauerhaft = $("#btn-dauerhaft").is(":checked") ? 1 : 0;

        if (!title || !preis) {
            $.amaran({
                'message': 'Titel und Preis erforderlich!',
                'position': 'bottom right',
                'inEffect': 'slideBottom'
            });
            return;
        }

        $.ajax({
            type: "POST",
            url: "./modules/AjaxController.php",
            data: {
                class: "CatererSpeiseplanArtikel",
                method: "addArtikel",
                params: {
                    titel: title,
                    descr: descr,
                    preis: preis,
                    dauerhaftesAngebot: dauerhaft
                }
            },
            success: function (data) {
                if (data == 1) {
                    $.amaran({
                        'message': 'Artikel angelegt!',
                        'position': 'bottom right',
                        'inEffect': 'slideBottom'
                    });
                    $("#title").val("");
                    $("#description").val("");
                    $("#preis").val("");
                    $("#btn-dauerhaft").attr("checked", false);
                    $("#lbl-dauerhaft").removeClass("active");
                    reloadArtikelTable();
                } else {
                    $.amaran({
                        'message': data,
                        'position': 'bottom right',
                        'inEffect': 'slideBottom'
                    });
                    reloadArtikelTable();
                }
            }
        });

    });
});

/**
 * Entfernt einen Artikel
 * @param artikelID ArtikelID
 */
function removeArtikel(artikelID) {
    if (!artikelID) {
        return;
    }
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererSpeiseplanArtikel",
            method: "removeArtikel",
            params: {
                id: artikelID
            }
        },
        success: function (success) {
            if (success) {
                $.amaran({
                    'message': 'Artikel gelöscht!',
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
                reloadArtikelTable();
            } else {
                $.amaran({
                    'message': success,
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
            }
        }
    });
}

/**
 * Aktualisiert die Artikel Tabelle
 */
function reloadArtikelTable() {
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererSpeiseplanArtikel",
            method: "createArtikelTable",
            params: {
                titel: 1
            }
        },
        success: function (ArtikelTableData) {
            $("#ArtikelTableData").html(ArtikelTableData);
        }
    });
}

/**
 * Öffnet das Artikel bearbeiten Modal
 * @param artikelID
 */
function openArtikelEditModal(artikelID) {
    if (!artikelID) {
        return;
    }

    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererSpeiseplanArtikel",
            method: "getArtikelEditModal",
            params: {
                id: artikelID
            }
        },
        success: function (modalHTML) {
            $("#ArtikelModalPlaceholder").html(modalHTML);
            $("#artikelEditModal").modal("show");
        }
    });
}

/**
 * Wenn Artikel Bearbeiten Modal geöffnet, werden die Werte ans PHP Backend gesendet
 * Speichert die Änderungen des Artikels
 * @param artikelID ArtikelID
 */
function saveArtikel(artikelID) {
    if (!artikelID) {
        return;
    }

    var title = $("#modalTitel").val();
    var descr = $("#modalDescr").val();
    var preis = $("#modalPreis").val();
    var dauerhaft = $("#modalDauerhaft").is(":checked") ? 1 : 0;

    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererSpeiseplanArtikel",
            method: "saveArtikel",
            params: {
                id: artikelID,
                title: title,
                descr: descr,
                preis: preis,
                dauerhaft: dauerhaft
            }
        },
        success: function (success) {
            console.log("JS Return", success);
            if (success == 1) {
                $.amaran({
                    'message': 'Änderungen wurden gespeichert!',
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
                reloadArtikelTable();
            } else {
                $.amaran({
                    'message': success,
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
            }
        }
    });
}