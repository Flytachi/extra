<!doctype html>
<html lang="en" class="no-focus">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

        <title>Phoenix - Медицинская платформа</title>

        <meta name="description" content="Phoenix - Медицинская платформа">
        <meta name="author" content="pixelcave">
        <meta name="robots" content="noindex, nofollow">

        <!-- Open Graph Meta -->
        <meta property="og:title" content="Phoenix - Медицинская платформа">
        <meta property="og:site_name" content="Phoenix">
        <meta property="og:description" content="Phoenix - Медицинская платформа">
        <meta property="og:type" content="website">
        <meta property="og:url" content="">
        <meta property="og:image" content="">

        <!-- Icons -->
        <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
        <link rel="shortcut icon" href="/static/assets/media/favicons/favicon.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/static/assets/media/favicons/favicon-192x192.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/static/assets/media/favicons/apple-touch-icon-180x180.png">
        <!-- END Icons -->

        <!-- Stylesheets -->

        <!-- Fonts and Codebase framework -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Muli:300,400,400i,600,700">
        <link rel="stylesheet" id="css-main" href="/static/assets/css/codebase.min.css">

    </head>
    <body>

        <div id="page-container" class="main-content-boxed">

            <!-- Main Container -->
            <main id="main-container">

                <!-- Page Content -->
                <div class="bg-image" style="background-image: url('/static/assets/media/photos/photo34@2x.jpg');">
                    <div class="row mx-0 bg-black-op">
                        <div class="hero-static col-md-6 col-xl-8 d-none d-md-flex align-items-md-end">
                            <div class="p-30 invisible" data-toggle="appear">
                                <p class="font-size-h3 font-w600 text-white">
                                    Get Inspired and Create.
                                </p>
                                <p class="font-italic text-white-op">
                                    Copyright &copy; <span class="js-year-copy"></span>
                                </p>
                            </div>
                        </div>
                        <div class="hero-static col-md-6 col-xl-4 d-flex align-items-center bg-white invisible" data-toggle="appear" data-class="animated fadeInRight">
                            <div class="content content-full">
                                <!-- Header -->
                                <div class="px-30 py-10">
                                    <a class="link-effect font-w700" href="/">
                                        <i class="si si-fire text-danger"></i>
                                        <span class="font-size-xl text-primary-dark">Phoe</span><span class="font-size-xl text-warning">nix</span>
                                    </a>
                                    <!-- <h1 class="h3 font-w700 mt-30 mb-10">Welcome to Your Dashboard</h1> -->
                                    <h2 class="h5 font-w400 text-muted mb-0">Авторизация</h2>
                                </div>
                                <!-- END Header -->

                                <form id="loginForm" class="js-validation-signin px-30" action="/auth/validate" method="post">
                                    <div class="form-group row">
                                        <div class="col-12">
                                            <div class="form-material floating">
                                                <input type="text" class="form-control" id="login-username" name="username" required>
                                                <label for="login-username">Username</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-12">
                                            <div class="form-material floating">
                                                <input type="password" class="form-control" id="login-password" name="password" required>
                                                <label for="login-password">Password</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-12">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="login-remember-me" name="login-remember-me">
                                                <label class="custom-control-label" for="login-remember-me">Запомнить меня</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-sm btn-hero btn-alt-primary">
                                            <i class="si si-login mr-10"></i> Вход
                                        </button>
                                        <!-- <div class="mt-30">
                                            <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="op_auth_reminder2.html">
                                                <i class="fa fa-warning mr-5"></i> Forgot Password
                                            </a>
                                        </div> -->
                                    </div>
                                </form>
                                <!-- END Sign In Form -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Page Content -->

            </main>
            <!-- END Main Container -->
        </div>
        <!-- END Page Container -->

        <script src="/static/assets/js/codebase.core.min.js"></script>
        <script src="/static/assets/js/codebase.app.min.js"></script>
        <script src="/static/assets/js/plugins/jquery-validation/jquery.validate.min.js"></script>
        <script src="/static/assets/js/pages/op_auth_signin.min.js"></script>
        <script src="/static/assets/js/plugins/bootstrap-notify/bootstrap-notify.min.js"></script>
        
        <script>

            $("#loginForm").on('submit', () => {
                event.preventDefault();
                $.ajax({
                    type: $(event.target).attr("method"),
                    url: $(event.target).attr("action"),
                    data: $(event.target).serializeArray(),
                    success: function (response) {
                        if (response.status == "success") {
                            Codebase.helpers('notify', {
                                align: 'right',
                                from: 'top',
                                type: response.status,
                                icon: 'fa fa-check mr-5',
                                message: response.message
                            });
                            setTimeout(function(){ 
                                location = "/";
                            }, 1000);
                        } else {
                            $("#loginForm").trigger("reset");
                            Codebase.helpers('notify', {
                                align: 'right',
                                from: 'top',
                                type: 'danger',
                                icon: 'fa fa-times mr-5',
                                message: response.message
                            });
                        }
                        
                    },
                });

            });

        </script>

    </body>
</html>