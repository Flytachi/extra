<?php

use Extra\Src\Controller;
use Extra\Src\Route;
use Extra\Src\CDO;

class AuthController extends Controller
{
    public function login(): void
    {
        $this->method(METHOD::GET);
        if (isset($_SESSION['id'])) Route::ErrorPage(423);
        $this->view('auth/login');
    }

    public function validate(): void
    {
        $this->method(METHOD::POST);
        if (empty($_POST)) $this->renderJsonError("Invalid request format.");
        if (empty($_POST['username'])) $this->renderJsonError("User 'username' not found.");
        if (empty($_POST['password'])) $this->renderJsonError("User 'password' not found.");

        $userModel = new UserRepository;
        $login = CDO::clean($_POST['username']);
        $password = CDO::clean($_POST['password']);
        $user = $userModel->getBy(['username' => $login, 'is_delete' => 0]);

        if ($user && password_verify($password, $user->password)) {
            $_SESSION['id'] = $user->id;
            $_SESSION['is_admin'] = $user->is_admin;
            $_SESSION['TZ'] = $_POST['TZ'] ?? null;
            if ($info = (new UserInfoRepository)->isUser($user->id)) $_SESSION['name'] = $info->name;
            $this->renderJsonSuccess('Verification was successful.');
        } else $this->renderJsonError("The entered data is not correct.");
    }

    public function logout(): void
    {
        $this->method(METHOD::GET);
        session_destroy();
        Route::redirect('auth/login');
    }
}