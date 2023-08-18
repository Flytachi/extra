<?php

namespace Extra\Src\Repo;

use Extra\Src\CDO\CDN;

trait RepositoryORMTrait
{
    final public function Select(string $option): Repository
    {
        $this->CRD_SQL['option'] = $option;
        return $this;
    }

    /**
     * Old method
     *
     * @param string $option
     * @return Repository
     */
    final public function Option(string $option): Repository
    {
        return $this->Select($option);
    }

    final public function As(string $table_as): Repository
    {
        $this->CRD_SQL['as'] = $table_as;
        return $this;
    }

    final public function Join(Repository $repository, string $on): Repository
    {
        $context = $repository::$table . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (array_key_exists('join', $this->CRD_SQL)) {
            $this->CRD_SQL['join'] .= ' JOIN ' . $context;
        } else $this->CRD_SQL['join'] = 'JOIN ' . $context;
        return $this;
    }

    final public function JoinLEFT(Repository $repository, string $on): Repository
    {
        $context = $repository::$table . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (array_key_exists('join', $this->CRD_SQL)) {
            $this->CRD_SQL['join'] .= ' LEFT JOIN ' . $context;
        } else $this->CRD_SQL['join'] = 'LEFT JOIN ' . $context;
        return $this;
    }

    final public function JoinRIGHT(Repository $repository, string $on): Repository
    {
        $context = $repository::$table . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (array_key_exists('join', $this->CRD_SQL)) {
            $this->CRD_SQL['join'] .= ' RIGHT JOIN ' . $context;
        } else $this->CRD_SQL['join'] = 'RIGHT JOIN ' . $context;
        return $this;
    }

    final public function Where(CDN $cdn): self
    {
        $this->CRD_SQL['where'] = 'WHERE ' . $cdn->getQuery();
        if (array_key_exists('binds', $this->CRD_SQL)) {
            $this->CRD_SQL['binds'] = [...$this->CRD_SQL['binds'], ...$cdn->getCache()];
        } else $this->CRD_SQL['binds'] = $cdn->getCache();
        return $this;
    }

    final public function Union(Repository $repository): Repository
    {
        if (array_key_exists('union', $this->CRD_SQL)) {
            $this->CRD_SQL['union'] .= ' UNION ' . $repository->getSql();
        } else $this->CRD_SQL['union'] = 'UNION ' . $repository->getSql();
        if (array_key_exists('binds', $this->CRD_SQL)) {
            $this->CRD_SQL['binds'] = [...$this->CRD_SQL['binds'], ...$repository->getSql('binds')];
        } else $this->CRD_SQL['binds'] = $repository->getSql('binds');
        return $this;
    }

    final public function Group(string $context): Repository
    {
        $this->CRD_SQL['group'] = 'GROUP BY ' . $context;
        return $this;
    }

    final public function Order(string $context): Repository
    {
        $this->CRD_SQL['order'] = 'ORDER BY ' . $context;
        return $this;
    }

    final public function Limit(int $limit, int $page = 1): Repository
    {
        if ($page < 1) $this->Throwable(new \TypeError('page < 1'));
        $this->CRD_SQL['page'] = $page;
        $this->CRD_SQL['limit'] = $limit;
        return $this;
    }
}