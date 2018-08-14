/**
 * Schaltet ein MOTD um.
 * @param motdID DatenbankID
 */
function toggleMotd(motdID) {
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererMOTD",
            method: "toggleMOTD",
            params: {
                id: motdID
            }
        },
        success: function () {
            location.reload();
        }
    });
}

/**
 * Sendet die Eingaben im Textfeld an PHP
 */
function motdAnlegen() {
    var msg = $("#msg").val();
    if (msg.length <= 0) {
        $.amaran({
            'message': 'Bitte eine Nachricht eingeben!',
            'position': 'bottom right',
            'inEffect': 'slideBottom'
        });
        return;
    }

    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererMOTD",
            method: "motdAnlegen",
            params: {
                msg: msg
            }
        },
        success: function (response) {
            if (response == 0) {
                $.amaran({
                    'message': 'Fehlerhafte Eingabe!',
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
            } else {
                location.reload();
            }

        }
    });
}