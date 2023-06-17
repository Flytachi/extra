<?php 

namespace Extra\Src;

use DateTime;
use DateTimeZone;
use Error;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use ReflectionClass;
use TypeError;
use Warframe;

/**
 *  Warframe collection
 *
 *  Wrapper
 *
 *  @version 4.0
 *  @author itachi
 *  @package Extra\Src
 */
class Wrapper
{
    static int $totalPages;
    static int $currentPage;
    static int $limitPage;
    static string $params;

    static function paginator(Repository $repo, string $func = 'getAll'): array
    {
        if (!$repo->getSql('limit')) throw new TypeError("Not value 'Limit'!");
        self::init($repo);
        return [
            'pageTotal' => self::$totalPages,
            'pageCurrent' => self::$currentPage,
            'pageElement' => self::$limitPage,
            'list' => $repo->{$func}(),
        ];
    }

    static function paginatorDecoration(Repository $repo, string $func = 'getAll'): array
    {
        if (!$repo->getSql('limit')) throw new TypeError("Not value 'Limit'!");
        return array(
            'table' => $repo->{$func}(),
            'panel' => Wrapper::panel($repo)
        );
    }

    final static function urlToArray(string $url): array
    {
        $code = explode('?', $url);
        $result = [];
        if (array_key_exists(1, $code)) {
            foreach (explode('&', $code[1]) as $param) {
                if ($param) {
                    $value = explode('=', $param);
                    $result[$value[0]] = $value[1];
                }
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

    #[NoReturn]
    private static function init(Repository $repo): void
    {
        $sql = $repo->buildSql();
        self::$currentPage = $repo->getSql('page');
        self::$limitPage = $repo->getSql('limit');
        self::$totalPages = ceil(
            Warframe::$db->query(substr($sql, 0, strpos($sql, 'LIMIT')))->rowCount() / self::$limitPage
        );
    }

    final static function pageAddon(string $url, int $value = 0): string
    {
        $local = Wrapper::urlToArray($url);
        $local['CRD_page'] = self::$currentPage + $value;
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
            self::init($repo);

            if (Wrapper::$totalPages <= 1) return '';
            $page = $repo->getSql('page');

            if ($page > Wrapper::$totalPages) $page = Wrapper::$totalPages;
            elseif ($page < 1) $page = 1;

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

    public static function formObject(object $object): object
    {
        $reflectionClass = new ReflectionClass(get_class($object));
        $array = array();
        foreach ($reflectionClass->getProperties() as $property) {
            try {
                if (str_contains((string)$property->getType(), '?'))
                    $value = !$property->getValue($object) ? null : $property->getValue($object);
                else $value = $property->getValue($object);
            } catch (Error) {
                $value = null;
            }
            $array[$property->getName()] = $value;
        }
        return (object) $array;
    }


    public static function dateConvertTo(string $dataTime, string $timeZone, string $format = 'Y-m-d H:i:s'): string
    {
        try {
            $date = new DateTime($dataTime, new DateTimeZone(Warframe::$cfg['GLOBAL_SETTING']['TIME_ZONE']));
            $date->setTimezone(new DateTimeZone($timeZone));
            return $date->format($format);
        } catch (Exception $e) {
            if ((Warframe::$cfg['GLOBAL_SETTING']['DEBUG'])) dd($e);
            else return '';
        }
    }

    public static function dateConvertToUTC(string $dataTime, string $timeZone, string $format = 'Y-m-d H:i:s'): string
    {
        try {
            $date = new DateTime($dataTime, new DateTimeZone($timeZone));
            $date->setTimezone(new DateTimeZone(Warframe::$cfg['GLOBAL_SETTING']['TIME_ZONE']));
            return $date->format($format);
        } catch (Exception $e) {
            if ((Warframe::$cfg['GLOBAL_SETTING']['DEBUG'])) dd($e);
            else return '';
        }
    }

    public static function isIntPositive(mixed $value): bool
    {
        if (!is_numeric($value)) return false;
        if ((int) $value > 0) return true;
        else return false;
    }

}
