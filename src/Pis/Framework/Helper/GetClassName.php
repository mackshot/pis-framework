<?php

namespace Pis\Framework\Helper;

trait GetClassName
{

    /**
     * @Options()
     * @return string
     */
    public static function GetClassName() {
        return get_called_class();
    }

}