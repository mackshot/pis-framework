<?php

namespace Pis\Framework\Entity;

/**
 * @Entity(repositoryClass="Pis\Framework\Entity\Repository\LanguageTranslationRepository")
 * @Table(name="language_translations",
 *      uniqueConstraints={@UniqueConstraint(name="token_language", columns={"token", "language"})})
 */
class LanguageTranslation extends BaseEntity
{

    public function __construct($language, $token, $text) {
        $this->language = $language;
        $this->token = $token;
        $this->translation = $text;
    }

    /** var integer @Id @Column(type="integer") @GeneratedValue */
    private $id;

    public function getId() {
        return $this->id;
    }

    /** var Language
     * @ManyToOne(targetEntity="Language")
     * @JoinColumn(name="language", referencedColumnName="id", nullable=false)
     */
    private $language;

    /**
     * @return Language
     */
    public function getLanguage() {
        return $this->language;
    }

    /** var LanguageToken
     * @ManyToOne(targetEntity="LanguageToken", inversedBy="translations")
     * @JoinColumn(name="token", referencedColumnName="id", nullable=false)
     */
    private $token;

    /**
     * @return LanguageToken
     */
    public function getToken() {
        return $this->token;
    }

    /** var string @Column(type="text") */
    private $translation;

    public function getTranslation() {
        return $this->translation;
    }
    public function setTranslation($translation) {
        $this->translation = $translation;
    }
}