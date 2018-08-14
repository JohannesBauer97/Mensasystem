$(document).ready(function () {

    /**
     * Event wenn ein Artikel aus dem Dropdown ausgewählt wurde
     * Sendet die Eingabe an PHP und wertet sie aus.
     */
    $(".speiseplan-artikel-select").change(function(ev){
        var select = $(ev.currentTarget);
        var artikelID = select.val();
        var date = select.attr("date");

        $.ajax({
            type: "POST",
            url: "./modules/AjaxController.php",
            data: {
                class: "CatererSpeiseplanVerwalten",
                method: "addArtikelTSpeiseplan",
                params: {
                    artikelID: artikelID,
                    date: date
                }
            },
            success: function (data) {
                if(data == 1){
                    select.val(-1);
                    $.amaran({
                        'message': 'Artikel hinzugefügt!',
                        'position': 'bottom right',
                        'inEffect': 'slideBottom'
                    });
                }else{
                    $.amaran({
                        'message': 'Artikel konnte nicht hinzugefügt werden!',
                        'position': 'bottom right',
                        'inEffect': 'slideBottom'
                    });
                }
                refreshSpeiseplanTable();
            }
        });

    });


    /**
     * Öffnet das Modal zum Artikel
     */
    $(document).on("click", ".speiseplan-artikel-btn", function(btn){
        btn = $(btn.currentTarget);
        var artikelID = btn.attr("artikelid");
        var speiseplanID = btn.attr("speiseplanid");

        $.ajax({
            type: "POST",
            url: "./modules/AjaxController.php",
            data: {
                class: "CatererSpeiseplanVerwalten",
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
 * Aktualisiert die Speiseplantabelle,
 * indem sie den HTML Code des Table-Bodys austauscht
 */
function refreshSpeiseplanTable(){
    var speisebody = $("#speiseplanTableBody");

    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererSpeiseplanVerwalten",
            method: "getSpeiseplanTableBody",
            params: {
                kw: getUrlParameter('kw'),
                jahr: getUrlParameter('jahr')
            }
        },
        success: function (data) {
            speisebody.html(data);
        }
    });
}

// Quelle: https://stackoverflow.com/questions/19491336/get-url-parameter-jquery-or-how-to-get-query-string-values-in-js
function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

/**
 * Löscht ein Element vom Speiseplan
 * beachtet dabei bestehende Buchungen
 * @param speiseplanID
 */
function deleteFromSpeiseplan(speiseplanID){
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererSpeiseplanVerwalten",
            method: "getBuchungen",
            params: {
                speise: speiseplanID
            }
        },
        success: function (buchungen) {
            if (buchungen > 0) {
                if (confirm(buchungen + " Kunden haben diesen Artikel gebucht.\nBestätigen Sie mit OK, dann werden alle Buchungen storniert und der Artikel gelöscht!")) {
                    $.ajax({
                        type: "POST",
                        url: "./modules/AjaxController.php",
                        data: {
                            class: "CatererSpeiseplanVerwalten",
                            method: "deleteFromSpeiseplan",
                            params: {
                                speise: speiseplanID
                            }
                        },
                        success: function (success) {
                            if (success == 1) {
                                $.amaran({
                                    'message': 'Artikel wurde entfernt!',
                                    'position': 'bottom right',
                                    'inEffect': 'slideBottom'
                                });
                            } else {
                                $.amaran({
                                    'message': 'Artikel konnte nicht entfernt werden!',
                                    'position': 'bottom right',
                                    'inEffect': 'slideBottom'
                                });
                            }
                            refreshSpeiseplanTable();
                        }
                    });
                }
            } else {
                $.ajax({
                    type: "POST",
                    url: "./modules/AjaxController.php",
                    data: {
                        class: "CatererSpeiseplanVerwalten",
                        method: "deleteFromSpeiseplan",
                        params: {
                            speise: speiseplanID
                        }
                    },
                    success: function (success) {
                        if (success == 1) {
                            $.amaran({
                                'message': 'Artikel wurde entfernt!',
                                'position': 'bottom right',
                                'inEffect': 'slideBottom'
                            });
                        } else {
                            $.amaran({
                                'message': 'Artikel konnte nicht entfernt werden!',
                                'position': 'bottom right',
                                'inEffect': 'slideBottom'
                            });
                        }
                        refreshSpeiseplanTable();
                    }
                });
            }

        }
    });
}