<?php

namespace Pis\Framework;

class Constants
{

    static $DATETIME_MIN;
    static $DATETIME_MAX;

    public static function init() {
        self::$DATETIME_MIN = new \DateTime('0000-01-01 00:00:00');
        self::$DATETIME_MAX = new \DateTime('9999-12-31 23:59:59');

    }
}

Constants::init();