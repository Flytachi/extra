<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Unit;

use Flytachi\Extra\Src\Factory\Connection\Repository\Interfaces\RepositoryInterface;
use TypeError;

/**
 * Class Wrapper
 *
 * The `Wrapper` class provides a set of utility functions associated with pagination.
 *
 * The methods provided by `Wrapper` include:
 *
 * - `paginator(Repository $repo, ?int $limit = null, int $page = 1, ?string $modelClassName = null): array`:
 * Generates the pagination links for a repository results.
 * - `paginatorDecoration(Repository $repo, ?int $limit = null, int $page = 1, ?string $modelClassName = null): array`:
 * Creates a decoration for the paginator.
 * - `panel(Repository $repo): string`: Builds the pagination panel.
 * - `urlToArray(string $url): array`: Converts the URL query string to an associative array.
 * - `arrayToUrl(array $get): string`: Converts an associative array of parameters to a URL query string.
 *
 * @version 2.4
 * @author Flytachi
 */
final class Wrapper
{
    private static int $maxList = 10;
    private static int $prevElement = 1;
    private static int $midElement = 5;
    private static int $nextElement = 1;
    private static int $maxListValue;
    private static int $totalPages;
    private static int $totalItem;
    private static int $currentPage;
    private static int $limitPage;
    private static string $params;

    /**
     * Paginate the results of a repository query.
     *
     * @param array|RepositoryInterface $repo The repository to paginate the results from.
     * @param int|null $limit The maximum number of items per page. If null,
     * the repository's default limit will be used.
     * @param int $page The current page number.
     * @param string|null $modelClassName The name of the model.
     *
     * @return array The paginated results as an associative array, with the following keys:
     * - pagination: An array containing information about the pagination, including:
     *   - current: The current page number.
     *   - previous: The previous page number. If there is no previous page, this will be 0.
     *   - next: The next page number. If there is no next page, this will be 0.
     *   - perPage: The maximum number of items per page.
     *   - totalItem: The total number of items.
     *   - totalPage: The total number of pages.
     * - list: An array of items fetched from the repository using the specified method.
     *
     * @throws TypeError If the limit is not set and the repository does not have a default limit.
     */
    final public static function paginator(
        array|RepositoryInterface $repo,
        ?int $limit = null,
        int $page = 1,
        ?string $modelClassName = null
    ): array {
        if ($repo instanceof RepositoryInterface) {
            if (!is_null($limit)) {
                $repo->limit($limit, $limit * ($page - 1));
            } else {
                if (!$repo->getSql('limit')) {
                    throw new TypeError("Not value 'Limit'!");
                }
            }
            self::init($repo);
            return [
                'pagination' => [
                    'current' => self::$currentPage,
                    'previous' => self::$currentPage - 1,
                    'next' => (self::$totalPages > self::$currentPage) ? self::$currentPage + 1 : 0,
                    'perPage' => self::$limitPage,
                    'totalItem' => self::$totalItem,
                    'totalPage' => self::$totalPages,
                ],
                'list' => $repo->findAll($modelClassName),
            ];
        } else {
            if (is_null($limit)) {
                throw new TypeError("Not value 'Limit'!");
            }
            $totalItem = count($repo);
            $totalPage = ceil($totalItem / $limit);
            $offset = $limit * ($page - 1);

            return [
                'pagination' => [
                    'current' => $page,
                    'previous' => $page - 1,
                    'next' => ($totalPage > $page) ? $page + 1 : 0,
                    'perPage' => $limit,
                    'totalItem' => $totalItem,
                    'totalPage' => ceil(
                        $totalItem / $limit
                    ),
                ],
                'list' => array_splice($repo, $offset, $limit),
            ];
        }
    }

    /**
     * Decorates the result of a paginator with a table and a panel.
     *
     * @param RepositoryInterface $repo The repository object to be paginated.
     * @param int|null $limit The limit of the pagination. If null, the limit is retrieved from the repository object.
     * @param int $page The current page of the pagination.
     * @param string|null $modelClassName The name of the model.
     * @return array An array containing the decorated pagination result,
     * with a 'table' key for the table data and a 'panel' key for the panel data.
     * @throws TypeError if the limit is null and the repository object does not have a 'limit' property set.
     */
    final public static function paginatorDecoration(
        RepositoryInterface $repo,
        ?int $limit = null,
        int $page = 1,
        ?string $modelClassName = null
    ): array {
        if (!is_null($limit)) {
            $repo->limit($limit, $limit * ($page - 1));
        } else {
            if (!$repo->getSql('limit')) {
                throw new TypeError("Not value 'Limit'!");
            }
        }
        self::init($repo);
        return array(
            'table' => $repo->findAll($modelClassName),
            'panel' => Wrapper::panel($repo)
        );
    }

    /**
     * @param RepositoryInterface $repo
     * @return string
     */
    final public static function panel(RepositoryInterface $repo): string
    {
        if (Wrapper::$totalPages <= 1) {
            return '';
        }
        Wrapper::$params = Wrapper::arrayToUrl($_GET);

        return sprintf(
            "<ul class=\"pagination pagination-flat pagination-rounded align-self-center justify-content-center mt-3\" >%s</ul>",
            Wrapper::buildPanel()
        );
    }

    final public static function urlToArray(string $url): array
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

    final public static function arrayToUrl(array $get): string
    {
        $str = "?";
        foreach ($get as $key => $value) {
            $str .= "$key=$value&";
        }
        return substr($str, 0, -1);
    }

    private static function pageSetter(int $number): string
    {
        $local = Wrapper::urlToArray(self::$params);
        $local['CRD_page'] = $number;
        return Wrapper::arrayToUrl($local);
    }

    private static function init(RepositoryInterface $repo): void
    {
        $countBody = '*';
        $sql = $repo->buildSql();
        if (str_contains($sql, 'LIMIT')) {
            $sql = strstr($sql, 'LIMIT', true);
        }
        if (str_contains($sql, 'ORDER')) {
            $sql = strstr($sql, 'ORDER', true);
        }
        if (str_contains($sql, 'GROUP BY')) {
            $countBody = trim(str_replace('GROUP BY', 'DISTINCT', strstr($sql, 'GROUP BY', false)));
            $sql = strstr($sql, 'GROUP BY', true);
        }

        $sql = 'SELECT COUNT(' . $countBody . ') ' . strstr($sql, 'FROM');
        self::$limitPage = (int) $repo->getSql('limit');
        self::$currentPage = (self::$limitPage + $repo->getSql('offset')) / self::$limitPage;

        $stmt = $repo->db()->prepare($sql);
        // Bind
        if ($repo->getSql('binds')) {
            foreach ($repo->getSql('binds') as $hash => $value) {
                $stmt->bindValue($hash, $value);
            }
        }
        $stmt->execute();
        self::$totalItem = $stmt->fetchColumn();
        self::$totalPages = (int) ceil(
            self::$totalItem / self::$limitPage
        );
    }

    private static function buildPanel(): string
    {
        $panel = '';

        self::$maxListValue = self::$prevElement + self::$midElement + self::$prevElement + 2;
        if (self::$maxList > self::$maxListValue) {
            self::$maxListValue = self::$maxList;
        }

        self::buildPanelPrev($panel);
        self::buildPanelMidl($panel);
        self::buildPanelNext($panel);

        return $panel;
    }

    private static function buildPanelPrev(string &$panel): void
    {
        if (self::$totalPages > self::$maxListValue) {
            if (self::$currentPage > 1) {
                $panel .=
                self::templateBtn(false, self::pageSetter(self::$currentPage - 1), "&larr; &nbsp; Prev");
            }

            // Elements
            for ($i = 1; $i <= self::$prevElement; $i++) {
                $panel .= self::templateBtn(self::$currentPage == $i, self::pageSetter($i), $i);
            }
        }
    }

    private static function buildPanelNext(string &$panel): void
    {
        if (self::$totalPages > self::$maxListValue) {
            // Elements
            for ($i = self::$totalPages - self::$nextElement + 1; $i <= self::$totalPages; $i++) {
                $panel .= self::templateBtn(self::$currentPage == $i, self::pageSetter($i), $i);
            }

            if (self::$currentPage < self::$totalPages) {
                $panel .=
                self::templateBtn(false, self::pageSetter(self::$currentPage + 1), "Next &nbsp; &rarr;");
            }
        }
    }

    private static function buildPanelMidl(string &$panel): void
    {
        if (self::$totalPages <= self::$maxListValue) {
            for ($i = 1; $i <= self::$totalPages; $i++) {
                $panel .= self::templateBtn(self::$currentPage == $i, self::pageSetter($i), $i);
            }
        } else {
            $midTemp = (int) ceil((self::$midElement - 1) / 2);
            self::buildPanelMidlBack($panel, $midTemp);
            self::buildPanelMidlCenter($panel, $midTemp);
            self::buildPanelMidlNext($panel, $midTemp);
        }
    }

    private static function buildPanelMidlBack(string &$panel, $midTemp): void
    {
        if (
            self::$prevElement != 0 &&
            (
                self::$currentPage <= self::$prevElement || self::$currentPage > self::$prevElement + $midTemp + 2
            )
        ) {
            $panel .= self::capBtn();
        }
        if (self::$currentPage == self::$prevElement + $midTemp + 2) {
            $panel .= self::templateBtn(false, self::pageSetter(self::$prevElement + 1), self::$prevElement + 1);
        }
    }

    private static function buildPanelMidlCenter(string &$panel, int $midTemp): void
    {
        $elementNext = self::$totalPages - self::$nextElement;

        if (
            self::$currentPage <= self::$prevElement
            || self::$currentPage > $elementNext
        ) {
            $startCenter = floor((self::$totalPages - self::$midElement) / 2) + 1;
            for ($i = $startCenter; $i < $startCenter + self::$midElement; $i++) {
                $panel .= self::templateBtn(self::$currentPage == $i, self::pageSetter($i), $i);
            }
        } else {
            $startPage = self::$currentPage - $midTemp;
            $endPage = self::$currentPage + $midTemp;

            for ($i = $startPage; $i <= $endPage; $i++) {
                if ($i > self::$prevElement && $i <= $elementNext) {
                    $panel .= self::templateBtn(self::$currentPage == $i, self::pageSetter($i), $i);
                }
            }
        }
    }

    private static function buildPanelMidlNext(string &$panel, $midTemp): void
    {
        if (
            self::$nextElement != 0 &&
            (
                self::$currentPage >= self::$totalPages - self::$nextElement + 1 ||
                self::$currentPage < self::$totalPages - self::$nextElement - $midTemp - 1
            )
        ) {
            $panel .= self::capBtn();
        }

        if (self::$currentPage == self::$totalPages - self::$nextElement - $midTemp - 1) {
            $panel .= self::templateBtn(
                false,
                self::pageSetter(self::$totalPages - self::$nextElement),
                (string) self::$totalPages - self::$nextElement
            );
        }
    }

    private static function templateBtn(bool $status, string $url, string $text): string
    {
        return "<li class=\"page-item " . (($status) ? 'active' : '') . "\">"
            . "<a onclick=\"credoSearch('{$url}')\" class=\"page-link\">{$text}</a>"
            . "</li>";
    }

    private static function capBtn(): string
    {
        return "<li class=\"page-item\">"
            . "<a onclick=\"return 0;\" class=\"page-link\">...</a>"
            . "</li>";
    }

    /**
     * Sets the maximum number of items in a list.
     *
     * @param int $maxList The maximum number of items to be displayed in a list.
     * @return void
     */
    public static function setMaxList(int $maxList): void
    {
        self::$maxList = $maxList;
    }

    /**
     * Sets the previous element value.
     *
     * @param int $prevElement The value to set as the previous element.
     * @return void
     */
    public static function setPrevElement(int $prevElement): void
    {
        self::$prevElement = $prevElement;
    }

    /**
     * Sets the value for the central element.
     *
     * @param int $midElement The value to be set as the central element.
     * @return void
     */
    public static function setMidElement(int $midElement): void
    {
        self::$midElement = $midElement;
    }

    /**
     * Sets the next element to be used.
     *
     * @param int $nextElement The value to set as the next element.
     *
     * @return void
     */
    public static function setNextElement(int $nextElement): void
    {
        self::$nextElement = $nextElement;
    }
}
