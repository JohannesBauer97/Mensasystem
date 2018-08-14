$(document).ready( function () {
    /**
     * Datatable Plugin auf KundenTable initialisieren
     */
    $('#KundenTable').DataTable();

    /**
     * Erstellen des Vorgeschlagenen Accountnamen
     */
    $("#vorname").keyup(function(ev){
        var accountname = $("#vorname").val() + "." + $("#nachname").val();
        $("#accountname").val(accountname);
    });

    /**
     * Erstellen des Vorgeschlagenen Accountnamen
     */
    $("#nachname").keyup(function(ev){
        var accountname = $("#vorname").val() + "." + $("#nachname").val();
        $("#accountname").val(accountname);
    });

    /**
     * Klick Event für den Abbrechen Button
     * Resettet alle Felder im Kunden Anlegen Formular
     */
    $("#kf-abbrechen").click(function(){
        $("#vorname").val("");
        $("#nachname").val("");
        $("#accountname").val("");
        $("#kontostand").val(0);
    });

    /**
     * Klick Event für den Kunden anlegen Button
     * Sendet die Daten aus dem Formularfeldern an PHP
     */
    $("#kf-anlegen").click(function(){
        $.ajax({
            type: "POST",
            url: "./modules/AjaxController.php",
            data: {
                class: "CatererKunden",
                method: "addKunde",
                params: {
                    vorname:$("#vorname").val(),
                    nachname:$("#nachname").val(),
                    accountname:$("#accountname").val(),
                    kontostand:$("#kontostand").val()
                }
            },
            success: function(data){
                data = JSON.parse(data);
                if(data['error']){
                    var error = data['error'];
                    if(error.includes("Duplicate")){
                        $.amaran({
                            'message': 'Der Accountname existiert bereits.',
                            'position': 'bottom right',
                            'inEffect': 'slideBottom'
                        });
                    }else{
                        $.amaran({
                            'message': 'Fehler beim anlegen!',
                            'position': 'bottom right',
                            'inEffect': 'slideBottom'
                        });
                    }


                }else if(data["password"]){
                    $.amaran({
                        'message': 'User wurde angelegt!',
                        'position': 'bottom right',
                        'inEffect': 'slideBottom'
                    });

                    $("#res_accountname").html(data["accountname"]);
                    $("#res_password").html(data["password"]);
                    $("#res").show();
                    $("#vorname").val("");
                    $("#nachname").val("");
                    $("#accountname").val("");
                    $("#kontostand").val(0);
                    //location.reload();
                    //console.log("Passwort:", data["password"]);
                }
                reloadKundenTable();
            }
        });

    });

} );

/**
 * Löscht einen Kunden
 * @param kundenID KundenID
 */
function kundeLoeschen(kundenID){
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererKunden",
            method: "deleteKunde",
            params: {
                id: kundenID
            }
        },
        success: function(data){
            //console.log(data);
            if(data == 1) {
                $.amaran({
                    'message': 'Kunde gelöscht',
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
            }else{
                $.amaran({
                    'message': 'Fehler beim löschen ' + data,
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
            }
            reloadKundenTable();
        }
    });

}

/**
 * Zeigt das Passwort zurücksetzen Modal an
 * @param kundenID
 */
function kundePassReset(kundenID){
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererKunden",
            method: "getPassResetModal",
            params: {
                id:kundenID
            }
        },
        success: function(data){
            $("#modalPlaceholder").html(data);
            $("#passResetModal").modal("show");
        }
    });
}

/**
 * Fordert ein neues Passwort für einen Kunden an
 * @param kundenID
 */
function resetPass(kundenID){
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererKunden",
            method: "passReset",
            params: {
                id:kundenID
            }
        },
        success: function(pw){
            if(pw.length > 0){
                $("#newPassP").fadeIn();
                $("#newPassS").html(pw);
            }else{
                $("#errorPass").fadeIn();
            }
        }
    });
}

/**
 * Lädt das Konto eines Kunden um den Betrag im Modal auf
 * @param kundenID
 */
function kundeAufladen(kundenID){
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererKunden",
            method: "kundeAufladen",
            params: {
                id:kundenID,
                betrag: $("#Betrag").val()
            }
        },
        success: function(success){

            if(success[0] != "["){
                //ERROR
                $.amaran({
                    'message': success,
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
                return;
            }

            success = JSON.parse(success);
            //console.log(success);
            if(success[0]){
                var kontostand = parseFloat(Math.round(success[1] * 100) / 100).toFixed(2);

                $("#kontostandModal").html(kontostand.replace(".",",") + "€");
                $.amaran({
                    'message': 'Kontostand erhöht!',
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });

            }else{
                $.amaran({
                    'message': 'Kontostand konnte nicht erhöht werden!',
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
            }
            reloadKundenTable();
        }
    });

}

/**
 * Entlädt das Konto eines Kunden um den Betrag im Modal
 * @param kundenID
 */
function kundeEntladen(kundenID){
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererKunden",
            method: "kundeEntladen",
            params: {
                id:kundenID,
                betrag: $("#Betrag").val()
            }
        },
        success: function(success){

            if(success[0] != "["){
                //ERROR meldung
                $.amaran({
                    'message': success,
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
                return;
            }

            success = JSON.parse(success);
            //console.log(success);
            if(success[0]){
                var kontostand = parseFloat(Math.round(success[1] * 100) / 100).toFixed(2);

                $("#kontostandModal").html(kontostand.replace(".",",") + "€");
                $.amaran({
                    'message': 'Kontostand verringert!',
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });

            }else{
                $.amaran({
                    'message': 'Kontostand konnte nicht verringert werden!',
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
            }
            reloadKundenTable();
        }
    });
}

/**
 * Öffnet das Konto Modal
 * @param kundenID KundenID
 * @param aufladen True öffnet das aufladen Modal, False öffnet das entladen Modal
 */
function openKontoModal(kundenID, aufladen){
    aufladen ? aufladen = 1 : aufladen = 0;
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererKunden",
            method: "getkontoModal",
            params: {
                id:kundenID,
                aufladen: aufladen
            }
        },
        success: function(modalHTML){
            $("#modalPlaceholder").html(modalHTML);
            $("#kontoModal").modal("show");
        }
    });
}

/**
 * Öffnet das Stammdaten Modal
 * @param kundenID
 */
function openStammdatenModal(kundenID){
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererKunden",
            method: "getStammdatenModal",
            params: {
                id:kundenID
            }
        },
        success: function(modalHTML){
            $("#modalPlaceholder").html(modalHTML);
            $("#stammdatenModal").modal("show");
        }
    });
}

/**
 * Sendet die neuen Stammdaten des Kunden an PHP
 * @param kundenID
 */
function saveStammdaten(kundenID){
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererKunden",
            method: "saveStammdaten",
            params: {
                id:kundenID,
                vname:$("#modalVorname").val(),
                nname:$("#modalNachname").val()
            }
        },
        success: function(success){
            if(success){
                $.amaran({
                    'message': 'Änderungen wurden gespeichert!',
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
            }else{
                $.amaran({
                    'message': success,
                    'position': 'bottom right',
                    'inEffect': 'slideBottom'
                });
            }
            reloadKundenTable();
        }
    });

}

/**
 * Aktualisiert die Kundentabelle mit Daten vom Server
 */
function reloadKundenTable(){
    $.ajax({
        type: "POST",
        url: "./modules/AjaxController.php",
        data: {
            class: "CatererKunden",
            method: "createKundenTable",
            params: {
                kk:"..."
            }
        },
        success: function(kundenTableHTML){
            $("#KundenTableData").html(kundenTableHTML);
        }
    });
}