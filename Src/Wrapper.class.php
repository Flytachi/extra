<?php 

namespace Extra\Src;

class Wrapper
{
    /**
     * 
     * Wrapper
     * 
     * @version 3.0
     */

    static int $totalPages;
    static string $params;
    
    static function paginator(Repository $repo, string $func = 'getAll'): array
    {
        return array(
            'table' => $repo->{$func}(),
            'panel' => Wrapper::panel($repo)
        );
    }

    final static function urlToArray(string $url): array
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

    final static function arrayToUrl(array $get): string
    {
        $str = "?";
        foreach ($get as $key => $value) $str .= "$key=$value&";
        return substr($str,0,-1);
    }

    final static function pageAddon(string $url, int $value = 0): string
    {
        $local = Wrapper::urlToArray($url);
        $local['CRD_page'] += $value;
        return Wrapper::arrayToUrl($local);
    }

    final static function pageSet(string $url, int $value = 0): string
    {
        $local = Wrapper::urlToArray($url);
        $local['CRD_page'] = $value;
        return Wrapper::arrayToUrl($local);
    }

    final static function panel(Repository $repo): string
    {
        if ($repo->getSql('limit') > 0) {
            $sql = $repo->buildSql();
            Wrapper::$totalPages = ceil(
                $repo->db->query(substr($sql, 0, strpos($sql, 'LIMIT')))->rowCount() / $repo->getSql('limit')
            );
            if (Wrapper::$totalPages <= 1) return '';
            if (isset($_GET['CRD_page'])) {
                $page = (int) $_GET['CRD_page'];
            }else $page = 1;

            if ($page > Wrapper::$totalPages) $page = Wrapper::$totalPages;
            elseif ($page < 1) $page = 1;

            if (empty($_GET['CRD_page'])) $_GET['CRD_page'] = 1;
            Wrapper::$params = Wrapper::arrayToUrl($_GET);

            return "<ul class=\"pagination pagination-flat pagination-rounded align-self-center justify-content-center mt-3\" >" .
                Wrapper::buildPanel($page) . "</ul>";
        }
    }

    final static function buildPanel(int $page): string
    {
        $selfPage = $firstBack = $nextLast = '';

        // prev
        if (Wrapper::$totalPages > 5) {
            if ($page > 1) {
                $firstBack = "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                    Wrapper::pageAddon(Wrapper::$params, -1) .
                    "')\" class=\"page-link\">&larr; &nbsp; Prev</a></li>";
            }
        }

        // left
        if ($page <= floor(Wrapper::$totalPages / 2)) {

            if (Wrapper::$totalPages == 5) {

                $selfPage = self::getStr($page, $selfPage);

            }elseif (Wrapper::$totalPages == 4) {

                $selfPage = self::getStr($page, $selfPage);
                
            }else {
                $selfPage .= "<li class=\"page-item active\"><a onclick=\"credoSearch('" . 
                    Wrapper::$params . "')\" class=\"page-link\">$page</a></li>";
                if (Wrapper::$totalPages > 4) $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                    Wrapper::pageAddon(Wrapper::$params, 1)."')\" class=\"page-link\">".($page+1)."</a></li>";
            }
        
        }else {
            $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" . 
                Wrapper::pageSet(Wrapper::$params, 1)."')\" class=\"page-link\">1</a></li>";
            if (Wrapper::$totalPages > 3) $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                Wrapper::pageSet(Wrapper::$params, 2)."')\" class=\"page-link\">2</a></li>";
        }

        // center
        if (Wrapper::$totalPages == 5) {

            $status = ($page == 3) ? "active" : ""; 
            $selfPage .= "<li class=\"page-item $status\"><a onclick=\"credoSearch('" .
                Wrapper::pageSet(Wrapper::$params, 3)."')\" class=\"page-link\">3</a></li>";
       
        }elseif (Wrapper::$totalPages > 4) {

            if ($page <= floor(Wrapper::$totalPages / 2)) $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                Wrapper::pageSet(Wrapper::$params, floor((Wrapper::$totalPages+$page)/2))."')\" class=\"page-link\">...</a></li>";
            else $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                Wrapper::pageSet(Wrapper::$params, floor(($page)/2))."')\" class=\"page-link\">...</a></li>";

        }elseif(Wrapper::$totalPages == 3) {

            $status = ($page == 2) ? "active" : ""; 
            $selfPage .= "<li class=\"page-item $status\"><a onclick=\"credoSearch('" .
                Wrapper::pageSet(Wrapper::$params, 2)."')\" class=\"page-link\">2</a></li>";
        
        }
        

        // right
        if ($page > floor(Wrapper::$totalPages / 2)) {

            if (Wrapper::$totalPages > 5) $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                Wrapper::pageAddon(Wrapper::$params, -1)."')\" class=\"page-link\">".($page-1)."</a></li>";
            
            if (Wrapper::$totalPages == 5) {

                if ($page == 4) {
                    $selfPage .= "<li class=\"page-item active\"><a onclick=\"credoSearch('" .
                        Wrapper::$params . "')\" class=\"page-link\">$page</a></li>";
                    $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                        Wrapper::pageAddon(Wrapper::$params, 1)."')\" class=\"page-link\">".($page+1)."</a></li>";
                }elseif($page == 5) {
                    $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                        Wrapper::pageAddon(Wrapper::$params, -1)."')\" class=\"page-link\">".($page-1)."</a></li>";
                    $selfPage .= "<li class=\"page-item active\"><a onclick=\"credoSearch('" .
                        Wrapper::$params . "')\" class=\"page-link\">$page</a></li>";
                }else {
                    $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                        Wrapper::pageAddon(Wrapper::$params, 1)."')\" class=\"page-link\">".($page+1)."</a></li>";
                    $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                        Wrapper::pageAddon(Wrapper::$params, 2)."')\" class=\"page-link\">".($page+2)."</a></li>";
                }
                
            }elseif (Wrapper::$totalPages == 4) {

                if ($page == 3) {
                    $selfPage .= "<li class=\"page-item active\"><a onclick=\"credoSearch('" . 
                        Wrapper::$params . "')\" class=\"page-link\">$page</a></li>";
                    $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                        Wrapper::pageAddon(Wrapper::$params, 1)."')\" class=\"page-link\">".($page+1)."</a></li>";
                }elseif($page == 4) {
                    $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                        Wrapper::pageAddon(Wrapper::$params, -1)."')\" class=\"page-link\">".($page-1)."</a></li>";
                    $selfPage .= "<li class=\"page-item active\"><a onclick=\"credoSearch('" . 
                        Wrapper::$params . "')\" class=\"page-link\">$page</a></li>";
                }
                
            }elseif (Wrapper::$totalPages == 3) {
                $status = ($page == 3) ? "active" : ""; 
                $selfPage .= "<li class=\"page-item $status\"><a onclick=\"credoSearch('" .
                    Wrapper::pageSet(Wrapper::$params, 3)."')\" class=\"page-link\">3</a></li>";
            }else $selfPage .= "<li class=\"page-item active\"><a onclick=\"credoSearch('" . 
                Wrapper::$params . "')\" class=\"page-link\">$page</a></li>";

        }else {
            if (Wrapper::$totalPages > 3) $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                Wrapper::pageSet(Wrapper::$params, Wrapper::$totalPages-1)."')\" class=\"page-link\">".(Wrapper::$totalPages-1)."</a></li>";
            $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                Wrapper::pageSet(Wrapper::$params, Wrapper::$totalPages)."')\" class=\"page-link\">" . Wrapper::$totalPages . "</a></li>";
        }
        
        // next
        if (Wrapper::$totalPages > 5) {
            if ($page < Wrapper::$totalPages) $nextLast =  "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                Wrapper::pageAddon(Wrapper::$params, 1)."')\" class=\"page-link\">Next &nbsp; &rarr;</a></li>";
        }

        return $firstBack.$selfPage.$nextLast;
    }

    /**
     * @param int $page
     * @param string $selfPage
     * @return string
     */
    public static function getStr(int $page, string $selfPage): string
    {
        if ($page == 1) {
            $selfPage .= "<li class=\"page-item active\"><a onclick=\"credoSearch('" .
                Wrapper::$params . "')\" class=\"page-link\">$page</a></li>";
            $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                Wrapper::pageAddon(Wrapper::$params, 1) . "')\" class=\"page-link\">" . ($page + 1) . "</a></li>";
        } elseif ($page == 2) {
            $selfPage .= "<li class=\"page-item\"><a onclick=\"credoSearch('" .
                Wrapper::pageAddon(Wrapper::$params, -1) . "')\" class=\"page-link\">" . ($page - 1) . "</a></li>";
            $selfPage .= "<li class=\"page-item active\"><a onclick=\"credoSearch('" .
                Wrapper::$params . "')\" class=\"page-link\">$page</a></li>";
        }
        return $selfPage;
    }

}
