<?php

namespace Pis\Framework\Entity;

use Pis\Framework\Helper\GetClassName;

class BaseEntity
{

    use GetClassName;

    public static function EntityName()
    {
        return self::getEntityName('F');
    }

    protected static function getEntityName($prefix)
    {
        $className = 'NOTDEFINED';
        if (preg_match('@\\\\([\w]+)$@', self::GetClassName(), $matches)) {
            $className = $matches[1];
        }
        return $prefix . ':' . $className;
    }

}