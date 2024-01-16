<?php

namespace Pis\Framework\Controller;

class Paginator extends \Doctrine\ORM\Tools\Pagination\Paginator
{

    protected $from;
    protected $length;

    public function __construct($query, $fetchJoinCollection = true, $from, $length) {
        parent::__construct($query, $fetchJoinCollection);
        $this->from = $from;
        $this->length = $length;
    }

    public function GetFrom() {
        return $this->from + 1;
    }

    public function GetTo() {
        return min($this->from + $this->length, $this->count());
    }

    public function GetLength() {
        return $this->length;
    }

    public function GetPageCount() {
        return ceil($this->count() / $this->length);
    }

    public function GetCurrentPage() {
        return (($this->from / $this->length) + 1);
    }

}