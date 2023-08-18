<?php 

namespace Extra\Src;

use DateTime;
use DateTimeZone;
use Error;
use Exception;
use Extra\Src\Repo\Repository;
use ReflectionClass;
use TypeError;
use Warframe;

/**
 *  Warframe collection
 *
 *  Wrapper
 *
 *  @version 7.0
 *  @author itachi
 *  @package Extra\Src
 */
class Wrapper
{
    const MAX_LIST = WRAPPER_PAGINATION_MAX_LIST;
    const PREV_ELEMENTS = WRAPPER_PAGINATION_PREV_ELEMENTS;
    const NEXT_ELEMENTS = WRAPPER_PAGINATION_NEXT_ELEMENTS;
    const MIDL_ELEMENTS = WRAPPER_PAGINATION_MIDL_ELEMENTS;

    static int $maxListValue;

    static int $totalPages;
    static int $currentPage;
    static int $limitPage;
    static string $params;

    public static function dateConvertTo(string $dataTime, string $timeZone, string $format = 'Y-m-d H:i:s'): string
    {
        try {
            $date = new DateTime($dataTime, new DateTimeZone(Warframe::$env['TIME_ZONE']));
            $date->setTimezone(new DateTimeZone($timeZone));
            return $date->format($format);
        } catch (Exception $e) {
            if ((Warframe::$env['DEBUG'])) dd($e);
            else return '';
        }
    }

    public static function dateConvertToUTC(string $dataTime, string $timeZone, string $format = 'Y-m-d H:i:s'): string
    {
        try {
            $date = new DateTime($dataTime, new DateTimeZone($timeZone));
            $date->setTimezone(new DateTimeZone(Warframe::$env['TIME_ZONE']));
            return $date->format($format);
        } catch (Exception $e) {
            if ((Warframe::$env['DEBUG'])) dd($e);
            else return '';
        }
    }

    public static function isIntPositive(mixed $value): bool
    {
        if (!is_numeric($value)) return false;
        if ((int) $value > 0) return true;
        else return false;
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

    final static function paginator(Repository $repo, string $func = 'getAll'): array
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

    final static function paginatorDecoration(Repository $repo): array
    {
        if (!$repo->getSql('limit')) throw new TypeError("Not value 'Limit'!");
        return array(
            'table' => $repo->getAll(),
            'panel' => Wrapper::panel($repo)
        );
    }

    final static function panel(Repository $repo): string
    {
        self::init($repo);
        if (Wrapper::$totalPages <= 1) return '';
        Wrapper::$params = Wrapper::arrayToUrl($_GET);

        return "<ul class=\"pagination pagination-flat pagination-rounded align-self-center justify-content-center mt-3\" >" .
            Wrapper::buildPanel()
            . "</ul>";
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

    private static function pageSetter(int $number): string
    {
        $local = Wrapper::urlToArray(self::$params);
        $local['CRD_page'] = $number;
        return Wrapper::arrayToUrl($local);
    }

    private static function init(Repository $repo): void
    {
        $sql = $repo->buildSql();
        self::$currentPage = $repo->getSql('page');
        self::$limitPage = $repo->getSql('limit');

        $stmt = $repo->db()->prepare(substr($sql, 0, strpos($sql, 'LIMIT')));
        // Bind
        if ($repo->getSql('binds')) {
            foreach ($repo->getSql('binds') as $hash => $value)
                $stmt->bindValue($hash, $value);
        }
        $stmt->execute();

        self::$totalPages = ceil(
            $stmt->rowCount() / self::$limitPage
        );
    }

    private static function buildPanel(): string
    {
        $panel = '';

        self::$maxListValue = self::PREV_ELEMENTS + self::MIDL_ELEMENTS + self::PREV_ELEMENTS + 2;
        if (self::MAX_LIST > self::$maxListValue) self::$maxListValue = self::MAX_LIST;

        self::buildPanelPrev($panel);
        self::buildPanelMidl($panel);
        self::buildPanelNext($panel);

        return $panel;
    }

    private static function buildPanelPrev(string &$panel): void
    {
        if (self::$totalPages > self::$maxListValue) {
            if (self::$currentPage > 1) $panel .=
                self::templateBtn(false, self::pageSetter(self::$currentPage - 1), "&larr; &nbsp; Prev");

            // Elements
            for ($i = 1; $i <= self::PREV_ELEMENTS; $i++) {
                $panel .= self::templateBtn(self::$currentPage == $i, self::pageSetter($i), $i);
            }
        }
    }

    private static function buildPanelNext(string &$panel): void
    {
        if (self::$totalPages > self::$maxListValue) {
            // Elements
            for ($i = self::$totalPages - self::NEXT_ELEMENTS+1; $i <= self::$totalPages; $i++) {
                $panel .= self::templateBtn(self::$currentPage == $i, self::pageSetter($i), $i);
            }

            if (self::$currentPage < self::$totalPages) $panel .=
                self::templateBtn(false, self::pageSetter(self::$currentPage + 1), "Next &nbsp; &rarr;");
        }
    }

    private static function buildPanelMidl(string &$panel): void
    {
        if (self::$totalPages <= self::$maxListValue) {
            for ($i = 1; $i <= self::$totalPages; $i++) {
                $panel .= self::templateBtn(self::$currentPage == $i, self::pageSetter($i), $i);
            }
        } else {
            $midTemp = ceil((self::MIDL_ELEMENTS-1) / 2);
            self::buildPanelMidlBack($panel, $midTemp);
            self::buildPanelMidlCenter($panel, $midTemp);
            self::buildPanelMidlNext($panel, $midTemp);
        }
    }

    private static function buildPanelMidlBack(string &$panel, $midTemp): void
    {
        if (
            self::PREV_ELEMENTS != 0 &&
            (
                self::$currentPage <= self::PREV_ELEMENTS || self::$currentPage > self::PREV_ELEMENTS + $midTemp + 2
            )
        )   $panel .= self::capBtn();
        if(self::$currentPage == self::PREV_ELEMENTS + $midTemp+2)
            $panel .= self::templateBtn(false, self::pageSetter(self::PREV_ELEMENTS+1), self::PREV_ELEMENTS+1);
    }

    private static function buildPanelMidlCenter(string &$panel, int $midTemp): void
    {
        $elementNext = self::$totalPages - self::NEXT_ELEMENTS;

        if (
            self::$currentPage <= self::PREV_ELEMENTS
            || self::$currentPage > $elementNext
        ) {
            $startCenter = floor((self::$totalPages - self::MIDL_ELEMENTS) / 2)+1;
            for ($i = $startCenter; $i < $startCenter + self::MIDL_ELEMENTS; $i++) {
                $panel .= self::templateBtn(self::$currentPage == $i, self::pageSetter($i), $i);
            }
        } else {
            $startPage = self::$currentPage - $midTemp;
            $endPage = self::$currentPage + $midTemp;

            for ($i = $startPage; $i <= $endPage; $i++) {
                if ($i > self::PREV_ELEMENTS && $i <= $elementNext)
                    $panel .= self::templateBtn(self::$currentPage == $i, self::pageSetter($i), $i);
            }
        }
    }

    private static function buildPanelMidlNext(string &$panel, $midTemp): void
    {
        if (
            self::NEXT_ELEMENTS != 0 &&
            (
                self::$currentPage >= self::$totalPages - self::NEXT_ELEMENTS + 1 ||
                self::$currentPage < self::$totalPages - self::NEXT_ELEMENTS - $midTemp - 1
            )
        )   $panel .= self::capBtn();

        if(self::$currentPage == self::$totalPages - self::NEXT_ELEMENTS - $midTemp - 1)
            $panel .= self::templateBtn(false, self::pageSetter(self::$totalPages - self::NEXT_ELEMENTS), self::$totalPages - self::NEXT_ELEMENTS);
    }

    private static function templateBtn(bool $status, string $url, string $text): string
    {
        return "<li class=\"page-item ". (($status) ? 'active' : '') . "\">"
                . "<a onclick=\"credoSearch('{$url}')\" class=\"page-link\">{$text}</a>"
            . "</li>";
    }

    private static function capBtn(): string
    {
        return "<li class=\"page-item\">"
            . "<a onclick=\"return 0;\" class=\"page-link\">...</a>"
            . "</li>";
    }

}
