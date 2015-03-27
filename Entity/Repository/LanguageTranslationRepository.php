<?php

namespace Pis\Framework\Entity\Repository;

use Pis\Framework\Entity\LanguageTranslation;

class LanguageTranslationRepository extends BaseRepository
{

    public function findAllByLocale($locale)
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        return $queryBuilder->select('t, l, to, d')
            ->from(LanguageTranslation::EntityName(), 't')
            ->join('t.language', 'l')
            ->join('t.token', 'to')
            ->join('to.domain', 'd')
            ->where('l.locale = :locale')->setParameter('locale', $locale)
            ->getQuery()
            ->getResult();
    }

}