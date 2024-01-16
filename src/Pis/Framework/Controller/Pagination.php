<?php

namespace Pis\Framework\Controller;

use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Request;

class Pagination
{

    private $from;
    private $length;
    private $fetchJoinCollection;

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $from
     * @param integer $length
     * @param bool $fetchJoinCollection
     */
    public function __construct(Request $request, $from = 0, $length = 25, $fetchJoinCollection = false) {
        if ($request->get('pFrom') > 0) $from = $request->get('pFrom');
        if ($request->get('pLength') > 0) $length = $request->get('pLength');
        if (($from % $length) == 0) $this->from = $from;
        $this->from = $from - ($from % $length);
        $this->length = $length;
        $this->fetchJoinCollection = $fetchJoinCollection;
    }

    /**
     * @param Query $query
     * @return Paginator
     */
    public function getPaginator(Query $query) {
        $query->setFirstResult($this->from);
        $query->setMaxResults($this->length);
        $paginator = new Paginator($query, $this->fetchJoinCollection, $this->from, $this->length);
        return $paginator;
    }

}