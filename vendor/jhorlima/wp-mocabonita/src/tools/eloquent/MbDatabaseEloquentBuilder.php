<?php

namespace MocaBonita\tools\eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * Main class of the MocaBonita DatabaseEloquentBuilder
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\tools\eloquent
 *
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 */
class MbDatabaseEloquentBuilder extends Builder
{
    /**
     * Paginate the given query.
     *
     * @param null  $perPage
     * @param array $columns
     * @param null  $pageName
     * @param null  $page
     *
     * @return LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = null, $page = null)
    {
        $pageName = is_string($pageName) ?: MbDatabaseQueryBuilder::getPagination();

        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $perPage = $perPage ?: $this->model->getPerPage();

        $query = $this->toBase();

        $total = $query->getCountForPagination();

        $results = $total ? $this->forPage($page, $perPage)->get($columns) : new Collection();

        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
}