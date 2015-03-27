<?php

namespace Pis\Framework\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class BaseRepository extends EntityRepository
{

    public function keyBy($list, $action) {
        $by = array();
        if (!empty($list)) {
            foreach ($list as $item) {
                $by[$action($item)] = $item;
            }
        }
        return $by;
    }

}