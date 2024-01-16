<?php

namespace Pis\Framework\Entity;

/**
 * @Entity(repositoryClass="Pis\Framework\Entity\Repository\LanguageRepository")
 * @Table(name="languages")
 */
class Language extends BaseEntity
{

    /** var integer @Id @Column(type="integer") @GeneratedValue */
    private $id;

    public function getId() {
        return $this->id;
    }

    /** var string @Column(type="string", length=5) */
    private $locale;

    public function getLocale() {
        return $this->locale;
    }

    public function setLocale($locale) {
        $this->locale = $locale;
    }

    /** var string @Column(type="string", length=200) */
    private $name;

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    /** var bool @Column(type="boolean", options={"default" = false}) */
    private $available;

    public function getAvailable() {
        return $this->available;
    }

    public function setAvailable($available) {
        $this->available = $available;
    }
}