<?php

namespace Pis\Framework\Router;

use Pis\Framework\Controller\LanguageController;
use Pis\Framework\Router\Route;

class LanguageRouter extends BaseRouter
{

    public function __construct($controller = null) {
        if ($controller == null)
            $controller = LanguageController::GetClassName();
        parent::__construct($controller);

        $this->AddRoute($this->Index());
        $this->AddRoute($this->Add());
        $this->AddRoute($this->Edit());
        $this->AddRoute($this->TranslateAll());
        $this->AddRoute($this->TranslateUntranslated());
        $this->AddRoute($this->SetTranslation());
        $this->AddRoute($this->GetTranslation());
        $this->AddRoute($this->TokenAdd());
    }

    public function Index() {
        return new Route('/languages',
            array('_controller' => array($this->controller, 'IndexAction'))
        );
    }

    public function Add() {
        return new Route('/language/add',
            array('_controller' => array($this->controller, 'AddAction'))
        );
    }

    public function Edit() {
        return new Route('/language/{id}/edit',
            array('_controller' => array($this->controller, 'EditAction')),
            array('id' => '[0-9]+')
        );
    }

    public function TranslateAll() {
        return new Route('/language/translate-all/from/{from}/to/{to}/{domain}',
            array('_controller' => array($this->controller, 'TranslateAllAction'), 'domain' => ''),
            array('from' => '[^/]+', 'to' => '[^/]+', 'domain' => '[^/]+')
        );
    }

    public function TranslateUntranslated() {
        return new Route('/language/translate-untranslated/from/{from}/to/{to}/{domain}',
            array('_controller' => array($this->controller, 'TranslateUntranslatedAction'), 'domain' => ''),
            array('from' => '[^/]+', 'to' => '[^/]+', 'domain' => '[^/]+')
        );
    }

    public function GetTranslation() {
        return new Route('/language/translate/lang/{lang}/get',
            array('_controller' => array($this->controller, 'GetTranslationAction')),
            array('lang' => '[0-9]+')
        );
    }

    public function SetTranslation() {
        return new Route('/language/translate/lang/{lang}/set',
            array('_controller' => array($this->controller, 'SetTranslationAction')),
            array('lang' => '[0-9]+')
        );
    }

    public function TokenAdd() {
        return new Route('/language/token/add',
            array('_controller' => array($this->controller, 'TokenAddAction'))
        );
    }

}