<?php

namespace Pis\Framework\Entity;

/**
 * @Entity(repositoryClass="Pis\Framework\Entity\Repository\LanguageTokenRepository")
 * @Table(name="language_tokens",
 *      uniqueConstraints={@UniqueConstraint(name="domain_token", columns={"domain", "token"})}))
 */
class LanguageToken extends BaseEntity
{

    public function __construct($domain, $token) {
        $this->domain = $domain;
        $this->token = $token;
    }

    /** var integer @Id @Column(type="integer") @GeneratedValue */
    private $id;

    public function getId() {
        return $this->id;
    }

    /** var string
     * @ManyToOne(targetEntity="LanguageDomain")
     * @JoinColumn(name="domain", referencedColumnName="id", nullable=false)
     */
    private $domain;

    /**
     * @return LanguageDomain
     */
    public function getDomain() {
        return $this->domain;
    }

    /** @Column(type="string", length=200, nullable=false) */
    private $token;

    public function getToken() {
        return $this->token;
    }

    /** @Column(type="string", nullable=true) */
    private $description;

    public function getDescription() {
        return $this->description;
    }

    /**
     * @var LanguageTranslation[]
     * @OneToMany(targetEntity="LanguageTranslation", mappedBy="token")
     */
    private $translations;

    public function getTranslations() {
        return $this->translations;
    }

}