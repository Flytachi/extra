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
        if (empty($_POST)) $this->renderJsonError("Не верный формат запроса!");
        if (isset($_POST['username']) && $_POST['username'] && isset($_POST['password']) && $_POST['password']) {

            $userModel = new UserRepository;
            $login = CDO::clean($_POST['username']);
            $password = CDO::clean($_POST['password']);
            $user = $userModel->getBy(array('username' => $login, 'is_delete' => 0));
            
            if ($user && password_verify($password, $user->getPassword())) {
                $_SESSION['id'] = $user->getId();
                $_SESSION['is_admin'] = $user->getIsAdmin();
                if ($info = (new UserInfoRepository)->isUser($user->getId())) {
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