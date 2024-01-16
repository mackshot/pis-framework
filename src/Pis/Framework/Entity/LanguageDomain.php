<?php

namespace Pis\Framework\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;

/**
 * @Entity(repositoryClass="Pis\Framework\Entity\Repository\LanguageDomainRepository")
 * @Table(name="language_domains")
 */
class LanguageDomain extends BaseEntity
{

    /** var string @Id @Column(type="string", length=16) */
    private $id;

    public function getId() {
        return $this->id;
    }

}