<?php

namespace Pis\Framework\Security;

use Pis\Framework\Exception\LogoutException;
use Symfony\Component\HttpFoundation\Request;

class Session extends \Symfony\Component\HttpFoundation\Session\Session
{

    public function __construct($name, $locale, Request $request, $lifetime) {
        parent::__construct();
        $this->setName($name);
        if (!$this->has('locale'))
            $this->set('locale', $locale);

        if ($this->getUserId() > 0 &&
            time() - $this->getMetadataBag()->getCreated() >= $lifetime &&
            time() - $this->getMetadataBag()->getLastUsed() > $lifetime
        ) {
            $this->invalidate();
            throw new LogoutException;
        }
        if ($request->getMethod() == "GET")
            $this->migrate(false, $lifetime);
    }

    public function getUserId() {
        return $this->get('id');
    }

    public function setUserId($id) {
        $this->set('id', $id);
    }

    public function getLocale() {
        return $this->get('locale');
    }

    public function setLocale($locale) {
        $this->set('locale', $locale);
    }

}