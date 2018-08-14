$(function () {
    /**
     * Login button event
     */
    $("#btn-login").click(function () {
        login();
    });

    /**
     * Hit enter key while focus is on passwort field, calls login function
     */
    $("#passwort").keyup(function(e){
        if(e.keyCode == 13){
            login();
        }
    });

    /**
     * Sends Ajax login request to backend
     */
    function login(){
        //$(".loader").css("display","initial").setTimeout(500);
        var username = $("#username").val();
        var passwort = $("#passwort").val();

        $.ajax({
            type: "POST",
            url: "./modules/AjaxController.php",
            data: {
                class: "Login",
                method: "ajaxLogin",
                params: {
                    username: username,
                    passwort: passwort
                }
            },
            success: function(data){
                if(data == 1){
                    $.amaran({
                        'message': 'Login erfolgreich',
                        'position': 'bottom right',
                        'inEffect': 'slideBottom'
                    });
                    window.location = "index.php?page=home";
                }else{
                    $.amaran({
                        'message': 'Login fehlgeschlagen',
                        'position': 'bottom right',
                        'inEffect': 'slideBottom'
                    });
                }
            }
        });
    }

});