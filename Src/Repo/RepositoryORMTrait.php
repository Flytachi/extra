<?php

namespace Extra\Src\Repo;

/**
 * Trait RepositoryORMTrait
 *
 * `RepositoryORMTrait` is a trait that provides a set of methods for building selectable SQL query chunks in a Repository.
 * SQL parameters are stored as array and can be used to construct a full SQL statement.
 *
 * The trait provides these methods:
 *
 * - `select(string $option): Repository`: Sets the column(s) to select.
 * - `as(string $alias): Repository`: Sets an alias for the table.
 * - `join(Repository $repository, string $on): Repository`: Sets a "JOIN" clause.
 * - `joinLeft(Repository $repository, string $on): Repository`: Sets a "LEFT JOIN" clause.
 * - `joinRight(Repository $repository, string $on): Repository`: Sets a "RIGHT JOIN" clause.
 * - `where(BKB $bkb): Repository`: Sets a "WHERE" clause using a `BKB` instance.
 * - `union(Repository $repository): Repository`: Sets a "UNION" clause with another `Repository` instance's SQL.
 * - `groupBy(string $context): Repository`: Sets a "GROUP BY" clause.
 * - `having(string $context): Repository`: Sets a "HAVING" clause.
 * - `orderBy(string $context): Repository`: Sets an "ORDER BY" clause.
 * - `limit(int $limit, int $offset = 0): Repository`: Sets a "LIMIT" clause, with an optional offset.
 * - `forBy(string $context): Repository`: Sets an "FOR" clause.
 *
 * @version 1.0
 * @author Flytachi
 */
trait RepositoryORMTrait
{
    /**
     * @param string $option
     * @return Repository
     */
    final public function select(string $option): Repository
    {
        $this->CRD_SQL['option'] = $option;
        return $this;
    }

    /**
     * @param string $alias
     * @return Repository
     */
    final public function as(string $alias): Repository
    {
        $this->CRD_SQL['as'] = $alias;
        return $this;
    }

    /**
     * @param Repository $repository
     * @param string $on
     * @return Repository
     */
    final public function join(Repository $repository, string $on): Repository
    {
        $context = $repository::$table . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (array_key_exists('join', $this->CRD_SQL)) {
            $this->CRD_SQL['join'] .= ' JOIN ' . $context;
        } else $this->CRD_SQL['join'] = 'JOIN ' . $context;
        return $this;
    }

    /**
     * @param Repository $repository
     * @param string $on
     * @return Repository
     */
    final public function joinLeft(Repository $repository, string $on): Repository
    {
        $context = $repository::$table . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (array_key_exists('join', $this->CRD_SQL)) {
            $this->CRD_SQL['join'] .= ' LEFT JOIN ' . $context;
        } else $this->CRD_SQL['join'] = 'LEFT JOIN ' . $context;
        return $this;
    }

    /**
     * @param Repository $repository
     * @param string $on
     * @return Repository
     */
    final public function joinRight(Repository $repository, string $on): Repository
    {
        $context = $repository::$table . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (array_key_exists('join', $this->CRD_SQL)) {
            $this->CRD_SQL['join'] .= ' RIGHT JOIN ' . $context;
        } else $this->CRD_SQL['join'] = 'RIGHT JOIN ' . $context;
        return $this;
    }

    /**
     * @param BKB $bkb
     * @return Repository
     */
    final public function where(BKB $bkb): Repository
    {
        if ($bkb->getQuery()){
            $this->CRD_SQL['where'] = 'WHERE ' . $bkb->getQuery();
            if (array_key_exists('binds', $this->CRD_SQL)) {
                $this->CRD_SQL['binds'] = [...$this->CRD_SQL['binds'], ...$bkb->getCache()];
            } else $this->CRD_SQL['binds'] = $bkb->getCache();
        }
        return $this;
    }

    /**
     * @param Repository $repository
     * @return Repository
     */
    final public function union(Repository $repository): Repository
    {
        if (array_key_exists('union', $this->CRD_SQL)) {
            $this->CRD_SQL['union'] .= ' UNION ' . $repository->getSql();
        } else $this->CRD_SQL['union'] = 'UNION ' . $repository->getSql();
        if (array_key_exists('binds', $this->CRD_SQL)) {
            $this->CRD_SQL['binds'] = [...$this->CRD_SQL['binds'], ...$repository->getSql('binds')];
        } else $this->CRD_SQL['binds'] = $repository->getSql('binds');
        return $this;
    }

    /**
     * @param string $context
     * @return Repository
     */
    final public function groupBy(string $context): Repository
    {
        $this->CRD_SQL['group'] = 'GROUP BY ' . $context;
        return $this;
    }

    /**
     * @param string $context
     * @return Repository
     */
    final public function having(string $context): Repository
    {
        $this->CRD_SQL['group'] = 'HAVING ' . $context;
        return $this;
    }

    /**
     * @param string $context
     * @return Repository
     */
    final public function orderBy(string $context): Repository
    {
        $this->CRD_SQL['order'] = 'ORDER BY ' . $context;
        return $this;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return Repository
     */
    final public function limit(int $limit, int $offset = 0): Repository
    {
        if ($limit < 1) $this->Throwable(new \TypeError('limit < 1'));
        if ($offset < 0) $this->Throwable(new \TypeError('offset < 0'));
        $this->CRD_SQL['limit'] = $limit;
        $this->CRD_SQL['offset'] = $offset;
        return $this;
    }

    /**
     * @param string $context
     * @return Repository
     */
    final public function forBy(string $context): Repository
    {
        $this->CRD_SQL['for'] = $context;
        return $this;
    }

}