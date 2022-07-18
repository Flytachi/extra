<?php

abstract class Model
{
    /**
     * 
     * Model
     * 
     * @version 9.3
     */

    protected string $table = '';
    
    private string $pk;
    private array $data = [];
    private string $CRD_as = '';
    private string $CRD_sql;
    private string $CRD_data = '*';
    private string $CRD_join = '';
    private string $CRD_where = '';
    private string $CRD_order = '';
    private string $CRD_group = '';
    private string $CRD_search = '';
    private string $CRD_searchGetName = 'CRD_search=';
    private int $CRD_limit = 0;
    private bool $CRD_error = false;
    public CDO $db;

    use 
        CredoQuery,
        CredoParams,
        CredoPanel,
        CredoHelp,
        ModelTSave,
        ModelTUpdate,
        ModelTDelete,
        ModelTJsonResponce;


    public function __construct($table_As = null)
    {
        if ($table_As) $this->CRD_as = $table_As;
        $this->debug = cfgGet()['GLOBAL_SETTING']['DEBUG'];
        $this->setCfg();
    }


    final public function setPk(string $pk): void
    {
        $this->pk = $pk;
    }
    
    final public function setData(array|object $data): void
    {
        $this->data = (array) $data;
    }

    final public function getPk(): string
    {
        return $this->pk;
    }

    final public function getTable(): string
    {
        return $this->table;
    }

    final public function getTableAs(): string
    {
        return $this->CRD_as;
    }

    final public function getData(String $item = null)
    {
        return ($item == null) ? $this->data : $this->data[$item] ?? null;
    }

    final public function setDataItem(String $item, $value = null): void
    {
        $this->data[$item] = $value;
    }

    final public function deleteDataItem(String $item): void
    {
        unset($this->data[$item]);
    }

    final public function save(array|object $data): string
    {
        $this->setData($data);
        $this->saveBefore();
        $this->saveBody();
        $this->saveAfter();
        return $this->getPk();
    }

    final public function update(string $pk, array|object $data): string
    {
        $this->setPk($pk);
        $this->setData($data);
        $this->updateBefore();
        $this->updateBody();
        $this->updateAfter();
        return $this->getPk();
    }

    final public function delete(string $pk): string
    {
        $this->setPk($pk);
        $this->deleteBefore();
        $this->deleteBody();
        $this->deleteAfter();
        return $this->getPk();
    }

    final public function csrfToken(): void
    {
        $token = bin2hex(random_bytes(24));
        $_SESSION['CSRFTOKEN'] =  $token;
        echo "<input type=\"hidden\" name=\"csrf_token\" value=\"$token\">";
    }

    final public function stop(): never
    {
        if($this->db->inTransaction()) $this->db->rollBack();
        exit;
    }

    final public function dd()
    {
        parad("Data", $this->getData());
        $this->stop();
    }

    public function setCfg(array $cfgDatabase = null): void
    {
        if ($cfgDatabase) {
            $this->db = new CDO($cfgDatabase, $this->debug);
        } else {
            $this->db = new CDO(cfgGet()['DATABASE'], $this->debug);
        }
    }

    private function generateSql(): void
    {
        try {
            $this->CRD_sql = "SELECT $this->CRD_data FROM $this->table $this->CRD_as";
            if($this->CRD_join)  $this->CRD_sql .= " " . $this->CRD_join;
            if($this->CRD_where) $this->CRD_sql .= " " . $this->CRD_where;
            if($this->CRD_group) $this->CRD_sql .= " " . $this->CRD_group;
            if($this->CRD_order) $this->CRD_sql .= " " . $this->CRD_order;
            $this->CRD_search = (isset($_GET['CRD_search']) and $_GET['CRD_search']) ? $this->CRD_searchGetName.$_GET['CRD_search'] : "";
        } catch (\Throwable $th) {
            if ($this->CRD_error) $this->errorX($th);
            else echo 'Ошибка в генерации скрипта <strong>"SQL"</strong>';
        }
        
    }

    private function clsDta($value = "")
    {
        if (!is_array($value)) {
            $value = trim($value);
            $value = stripslashes($value);
            $value = strip_tags($value);
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    private function pageAddon(string $url, int $value = 0): string
    {
        $local = $this->urlToArray($url);
        $local['CRD_page'] += $value;
        return $this->arrayToUrl($local);
    }

    private function pageSet(string $url, int $value = 0): string
    {
        $local = $this->urlToArray($url);
        $local['CRD_page'] = $value;
        return $this->arrayToUrl($local);
    }

    private function urlToArray(string $url): array
    {
        $code = explode('?', $url);
        $result = [];
        foreach (explode('&', $code[1]) as $param) {
            if ($param) {
                $value = explode('=', $param);
                $result[$value[0]] = $value[1];
            }
        }
        return $result;
    }

    private function arrayToUrl(array $get): string
    {
        $str = "?";
        foreach ($get as $key => $value) $str .= "$key=$value&";
        return substr($str,0,-1);
    }

    private function errorX($error): never
    {
        $message = "\t" . $error->getMessage();
        foreach ($error->getTrace() as $key => $value) {
            if ($key != 0) {
                $message .= "\n\t\t#" . $key . ' ';  
                if (isset($value['file']))     $message .= $value['file'];
                if (isset($value['line']))     $message .= '(' . $value['line'] . '): ';
                if (isset($value['class']))    $message .= $value['class'];
                if (isset($value['type']))     $message .= $value['type'];
                if (isset($value['function'])) $message .= $value['function'];
            }
        }
        die (parad('CREDO', $message));
    }

    function __destruct()
    {
        unset($this->db);   
    }

}

?>