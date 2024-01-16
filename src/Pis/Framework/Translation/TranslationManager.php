<?php

namespace Pis\Framework\Translation;

use Doctrine\ORM\EntityManager;
use Pis\Framework\Entity;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class TranslationManager implements LoaderInterface
{

    /** @var EntityManager $entityManager */
    private $em;

    /** @var Entity\Repository\LanguageTranslationRepository  */
    private $translationRepository;

    /** @var Entity\Repository\LanguageRepository  */
    private $languageRepository;

    /** @var Entity\Language[] */
    private $languages;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
        $this->translationRepository = $em->getRepository(Entity\LanguageTranslation::EntityName());
        $this->languageRepository = $this->em->getRepository(Entity\Language::EntityName());
        $this->languages = $this->languageRepository->findAll();
    }

    /**
     * @return Entity\Language[]
     */
    public function getLanguages() {
        return $this->languages;
    }

    function load($resource, $locale, $domain = 'messages') {
        /** @var Entity\LanguageTranslation[] $translations */
        $translations = $this->translationRepository->findAllByLocale($locale);

        $translationsByDomain = array();

        /** @var $translation Entity\LanguageTranslation */
        foreach($translations as $translation)
            $translationsByDomain[$translation->getToken()->getDomain()->getId()][$translation->getToken()->getToken()] = $translation->getTranslation();

        $catalogue = new MessageCatalogue($locale);
        foreach($translationsByDomain as $key => $value)
            $catalogue->add($value, $key);

        return $catalogue;
    }
}