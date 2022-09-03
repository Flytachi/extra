<?php

use Extra\Src\Controller;
use Extra\Src\Route;
use Extra\Src\CDO;

class AuthController extends Controller
{
    public function login()
    {
        if (isset($_SESSION['id'])) Route::ErrorPage(423);
        $this->view('auth/login');
    }

    public function validate()
    {
        if ($_POST['username'] and $_POST['password']) {

            $userModel = new UserRepository;
            $login = CDO::clean($_POST['username']);
            $password = sha1(CDO::clean($_POST['password']));
            
            if ($user = $userModel->getBy(array('username' => $login, 'password' => $password, 'is_delete' => 0))) {
                $_SESSION['id'] = $user->id;
                $_SESSION['is_admin'] = $user->is_admin;
                if ($info = (new UserInfoRepository)->isUser($user->id)) {
                    $_SESSION['name'] = $info->name;
                }
                $this->renderJsonSuccess('Верификация прошла успешно');
            } else $this->renderJsonError("Введенные данные не верны");
            
        } else $this->renderJsonError("Введенные данные не верны");
    }

    public function logout()
    {
        session_destroy();
        Route::redirect('auth/login');
    }
}

?>