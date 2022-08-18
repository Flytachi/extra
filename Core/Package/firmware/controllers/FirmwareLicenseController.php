<?php

use Extra\Src\Controller;
use Extra\Src\Route;
use Extra\Src\Wrapper;

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
        importRepository('FirmwareEnterpriseRepository');
        $this->repo->as('l');
        $this->repo->Option("l.id, e.name 'enterprise', l.series, l.date_from, l.date_to, l.is_delete");
        $this->repo->JoinLEFT(new FirmwareEnterpriseRepository('e'), 'e.id=l.enterprise_id');
        $this->repo->Limit(10);
        $this->view('firmware/license/table', Wrapper::paginator($this->repo));
    }

    public function get($pk = null)
	{
        Route::isAuthAdmin();
        importRepository('FirmwareEnterpriseRepository');
        if($pk) $object = $this->getElement($pk);
        $this->view('firmware/license/form', array(
            'model' => $object ?? new $this->repo->modelName,
            'enterpriseList' => (new FirmwareEnterpriseRepository)->getAllNotDelete(),
            'inputCsrf' => $this->csrfTokenGen()
        ));
	}

    public function getFile($pk)
    {
        Route::isAuthAdmin(1);
        $object = $this->getElement($pk);
        $license = array(
            'licenseFirmware' => EXTRA_KEY,
            'licenseDateFrom' => strtotime($object->date_from),
            'licenseDateTo' => strtotime($object->date_to),
            'motherboardSeries' => $object->series, // motherboardSeries()
        );
        header("Content-type: text/plain");
        header("Content-Disposition: attachment; filename=license.crt");
        echo bin2hex(zlib_encode(json_encode($license), ZLIB_ENCODING_DEFLATE));
    }
}

?>