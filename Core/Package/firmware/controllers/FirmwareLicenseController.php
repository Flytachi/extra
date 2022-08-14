<?php

use Extra\Src\Controller;
use Extra\Src\Route;

class FirmwareLicenseController extends Controller
{
    public bool $onHook = true;
	public bool $onAuthHook = true;

	public bool $onDelete = true;
	public bool $onAuthDelete = true;

    public bool $onRestore = true;
	public bool $onAuthRestore = true;
	
	public bool $onRemove = true;
	public bool $onAuthRemove = true;

    public function prepareAuth():void
    {
        Route::isAuthAdmin();
    }

    public function index()
    {
        Route::isAuthAdmin(1);
        $this->render('firmware/license/main');
    }

    public function list()
    {
        Route::isAuthAdmin();
        importModel('FirmwareEnterpriseModel');
        $this->model->as('l');
        $this->model->Data("l.id, e.name 'enterprise', l.series, l.date_from, l.date_to");
        $this->model->JoinLEFT(new FirmwareEnterpriseModel('e'), 'e.id=l.enterprise_id');
        $this->model->Where('l.is_delete IS NULL')->Limit(10);
        $this->view('firmware/license/table', $this->model);
    }

    public function get($pk = null)
	{
        Route::isAuthAdmin();
        importModel('FirmwareEnterpriseModel');
        if($pk) $this->getElement($pk);
        $this->view('firmware/license/form', array(
            'model' => $this->model,
            'enterpriseList' => (new FirmwareEnterpriseModel)->Where("is_delete IS NULL")->list()
        ));
	}

    public function getFile($pk)
    {
        Route::isAuthAdmin(1);
        $this->getElement($pk);
        $license = array(
            'licenseFirmware' => EXTRA_KEY,
            'licenseDateFrom' => strtotime($this->model->getData('date_from')),
            'licenseDateTo' => strtotime($this->model->getData('date_to')),
            'motherboardSeries' => $this->model->getData('series'), // motherboardSeries()
        );
        header("Content-type: text/plain");
        header("Content-Disposition: attachment; filename=license.crt");
        echo bin2hex(zlib_encode(json_encode($license), ZLIB_ENCODING_DEFLATE));
    }

}

?>