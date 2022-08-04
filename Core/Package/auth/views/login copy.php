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
                <div class="bg-body-dark bg-pattern" style="background-image: url('/static/assets/media/various/bg-pattern-inverse.png');">
                    <div class="row mx-0 justify-content-center">
                        <div class="hero-static col-lg-6 col-xl-4">
                            <div class="content content-full overflow-hidden">
                                <!-- Header -->
                                <div class="py-30 text-center">
                                    <a class="link-effect font-w700" href="index.html">
                                        <i class="si si-fire"></i>
                                        <span class="font-size-xl text-primary-dark">Phoe</span><span class="font-size-xl">nix</span>
                                    </a>
                                </div>
                                <!-- END Header -->

                                <!-- Sign In Form -->
                                <form id="loginForm" class="js-validation-signin" action="/auth/validate" method="post">
                                    <div class="block block-themed block-rounded block-shadow">
                                        <div class="block-header bg-gd-dusk">
                                            <h3 class="block-title">Please Sign In</h3>
                                            <div class="block-options">
                                                <button type="button" class="btn-block-option">
                                                    <i class="si si-wrench"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="block-content">
                                            <div class="form-group row">
                                                <div class="col-12">
                                                    <label for="login-username">Username</label>
                                                    <input type="text" class="form-control" id="login-username" name="username">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-12">
                                                    <label for="login-password">Password</label>
                                                    <input type="password" class="form-control" id="login-password" name="password">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-0">
                                                <div class="col-sm-6 d-sm-flex align-items-center push">
                                                    <div class="custom-control custom-checkbox mr-auto ml-0 mb-0">
                                                        <input type="checkbox" class="custom-control-input" id="login-remember-me" name="login-remember-me">
                                                        <label class="custom-control-label" for="login-remember-me">Remember Me</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 text-sm-right push">
                                                    <button type="submit" class="btn btn-alt-primary">
                                                        <i class="si si-login mr-10"></i> Sign In
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="block-content bg-body-light">
                                            <div class="form-group text-center">
                                                <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="op_auth_signup3.html">
                                                    <i class="fa fa-plus mr-5"></i> Create Account
                                                </a>
                                                <a class="link-effect text-muted mr-10 mb-5 d-inline-block" href="op_auth_reminder3.html">
                                                    <i class="fa fa-warning mr-5"></i> Forgot Password
                                                </a>
                                            </div>
                                        </div>
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