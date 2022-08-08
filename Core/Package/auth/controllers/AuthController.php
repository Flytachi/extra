<?php

use Extra\Src\Controller;
use Extra\Src\Route;
use Extra\Src\CDO;

class AuthController extends Controller
{
    public bool $onHook = false;
	public bool $onAuthHook = false;

	public bool $onDelete = false;
	public bool $onAuthDelete = false;

    public bool $onRestore = false;
	public bool $onAuthRestore = false;
	
	public bool $onRemove = false;
	public bool $onAuthRemove = false;

    public function login()
    {
        if (isset($_SESSION['id'])) Route::ErrorPage(423);
        $this->view('auth/login');
    }

    public function validate()
    {
        if ($_POST['username'] and $_POST['password']) {

            importModel('UserModel', 'UserInfoModel');
            $userModel = new UserModel;
            $login = CDO::clean($_POST['username']);
            $password = sha1(CDO::clean($_POST['password']));
            
            if ($user = $userModel->Where("username = '$login' AND password = '$password' AND is_delete IS NULL")->get()) {
                $_SESSION['id'] = $user->id;
                $_SESSION['is_admin'] = $user->is_admin;
                if ($info = (new UserInfoModel)->isUser($user->id)) {
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