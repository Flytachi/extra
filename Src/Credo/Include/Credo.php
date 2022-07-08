<?php

abstract class Credo implements CredoInterface
{

    private String $CRD_sql;
    private String $CRD_as = '';
    private String $CRD_data = '*';
    private String $CRD_join = '';
    private String $CRD_where = '';
    private String $CRD_order = '';
    private String $CRD_group = '';
    private String $CRD_search = '';
    private String $CRD_searchGetName = 'CRD_search=';
    private Int $CRD_limit = 0;
    private Bool $CRD_error = false;
    public PDO $db;

    use 
        CredoQuery,
        CredoParams,
        CredoPanel,
        CredoHelp;

    
    public function __construct($table_As = null) {
        $this->debug = cfgGet()['GLOBAL_SETTING']['DEBUG'];
        if ($table_As) $this->CRD_as = $table_As;
    }

    public function DatabaseCfg()
    {
        return cfgGet()['DATABASE'];
    }

    private function generateSql()
    {
        try {
            $this->CRD_sql = "SELECT $this->CRD_data FROM $this->table $this->CRD_as";
            if($this->CRD_join)  $this->CRD_sql .= " " . $this->CRD_join;
            if($this->CRD_where) $this->CRD_sql .= " " . $this->CRD_where;
            if($this->CRD_group) $this->CRD_sql .= " " . $this->CRD_group;
            if($this->CRD_order) $this->CRD_sql .= " " . $this->CRD_order;
            $this->CRD_search = (isset($_GET['CRD_search']) and $_GET['CRD_search']) ? $this->CRD_searchGetName.$_GET['CRD_search'] : "";
        } catch (\Throwable $th) {
            if ($this->CRD_error) $this->error($th);
            else echo 'Ошибка в генерации скрипта <strong>"SQL"</strong>';
        }
        
    }

    private function clsDta($value = "") {
        if (!is_array($value)) {
            $value = trim($value);
            $value = stripslashes($value);
            $value = strip_tags($value);
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    private function pageAddon(String $url, int $value = 0)
    {
        $local = $this->urlToArray($url);
        $local['CRD_page'] += $value;
        return $this->arrayToUrl($local);
    }

    private function pageSet(String $url, int $value = 0)
    {
        $local = $this->urlToArray($url);
        $local['CRD_page'] = $value;
        return $this->arrayToUrl($local);
    }

    private function urlToArray(string $url)
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

    private function arrayToUrl(array $get)
    {
        $str = "?";
        foreach ($get as $key => $value) $str .= "$key=$value&";
        return substr($str,0,-1);
    }

    private function error($error){
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