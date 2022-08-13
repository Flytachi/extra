<!doctype html>
<html lang="en" class="no-focus">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <title>Warframe - Login</title>
        <link rel="shortcut icon" href="/static/warframe/img/logo.png" type="image/x-icon">
        <link href="/static/warframe/css/login.css" rel="stylesheet" type="text/css">
    </head>
    <body>

        <div class="login-box">
            <h2><img src="/static/warframe/img/logo.png" alt="" width="25px"> Login</h2>
            <small id="messageArea"></small>
            
            <form id="loginForm" class="js-validation-signin px-30" action="/auth/validate" method="post">
                <div class="user-box">
                    <input type="text" name="username" required>
                    <label>Username</label>
                </div>
                <div class="user-box">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>
                
                <button type="submit"><span></span><span></span><span></span><span></span><b>Войти</b></button>
            </form>
            
        </div>

        <script src="/static/warframe/js/jquery-3.6.0.min.js"></script>
        <script>

            $("#loginForm").on('submit', () => {
                event.preventDefault();
                $.ajax({
                    type: $(event.target).attr("method"),
                    url: $(event.target).attr("action"),
                    data: $(event.target).serializeArray(),
                    success: function (response) {
                        if (response.status == "success") {
                            $("#messageArea").css('color', 'green');
                            $("#messageArea").html(response.message);
                            setTimeout(function(){ 
                                location = "/";
                            }, 1000);
                        } else {
                            $("#loginForm").trigger("reset");
                            $("#messageArea").css('color', 'red');
                            $("#messageArea").html(response.message);
                        }
                    },
                });

            });

        </script>

    </body>
</html>