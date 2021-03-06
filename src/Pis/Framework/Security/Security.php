<?php

namespace Pis\Framework\Security;

abstract class Security
{

    /** @var \Pis\Framework\Security\Session */
    public $session;
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    abstract public function isUser();

    abstract public function hasRole($role);

    abstract public function hasRoleStartingWith($role);

    /** @return string[] */
    abstract public function getRolesString();

    abstract public function getUser();

}