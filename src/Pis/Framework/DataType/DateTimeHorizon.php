<?php

namespace Pis\Framework\DataType;

use Pis\Framework\Constants;

class DateTimeHorizon
{

    protected $from;
    protected $till;

    public function __construct($from = null, $till = null) {
        if ($from == null)
            $from = Constants::$DATETIME_MIN;
        $this->from = $from;
        if ($till == null)
            $till = Constants::$DATETIME_MAX;
        $this->till = $till;
    }

    public function getFrom() {
        return $this->from;
    }

    public function getTill() {
        return $this->till;
    }

}