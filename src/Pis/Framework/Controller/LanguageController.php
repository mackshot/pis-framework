<?php

namespace Pis\Framework\Controller;

use Pis\Framework\Validator\Constraint\Length;
use Pis\Framework\Validator\Constraint\NotBlank;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pis\Framework\Annotation\ControllerActionOptions as Options;
use Pis\Framework\Annotation\ControllerActionSecurity as Security;

use Pis\Framework\Entity as Entity;
use Pis\Framework\Validator\Constraint as Constraint;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @Security(roles={"la", "lac"})
 */
class LanguageController extends BaseController
{

    /**
     * @Options(method={"GET"})
     * @param Request $request
     * @return Response
     */
    public function IndexAction(Request $request)
    {
        if (!$this->security->hasRoleStartingWith("lac"))
            throw new \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException($request->getUri());
        /** @var Entity\Repository\LanguageRepository $languageRepository */
        $languageRepository = $this->em->getRepository(Entity\Language::EntityName());
        /** @var Entity\Language[] $languages */
        $languages = $languageRepository->findAll();

        $query = "SELECT to, d, t, l ";
        $query .= "FROM " . Entity\LanguageToken::EntityName() . " AS to ";
        $query .= "LEFT JOIN " . Entity\LanguageDomain::EntityName() . " AS d WITH (to.domain = d.id) ";
        $query .= "LEFT OUTER JOIN " . Entity\LanguageTranslation::EntityName() . " AS t WITH (to.id = t.token) ";
        $query .= "LEFT JOIN " . Entity\Language::EntityName() . " AS l WITH (t.language = l.id) ";
        $query = $this->em->createQuery($query);
        $result = $query->getResult();
        $tokens = array();
        $domains = array();
        $translations = array();
        if (!empty($result)) {
            foreach ($result as $res) {
                if ($res instanceof Entity\LanguageToken)
                    $tokens[$res->getId()] = $res;
                else if ($res instanceof Entity\LanguageDomain)
                    $domains[$res->getId()] = $res;
                else if ($res instanceof Entity\LanguageTranslation)
                    $translations[$res->getLanguage()->getId()][$res->getId()] = $res;
            }
        }
        return $this->response(
            array(
                'languages' => $languages,
                'tokens' => $tokens,
                'translations' => $translations,
                'masterlanguage' => from($languages)->where(function (Entity\Language $l) {
                    return $l->getMaster() == true;
                })->first()),
            $this->breadCrumb
        );
    }

    /**
     * @Options(method={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function AddAction(Request $request)
    {
        if (!$this->security->hasRole("lac"))
            throw new \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException($request->getUri());
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->languageForm($this->router->ParseRoute('Language::Add'));
        if ($request->getMethod() === 'POST') {
            $form->handleRequest();
            $data = $form->getData();
            /** @var Entity\Repository\LanguageRepository $languageRepository */
            $languageRepository = $this->em->getRepository(Entity\Language::EntityName());
            $language = $languageRepository->findOneBy(array('locale' => $data['locale']));
            if ($language !== null)
                $form->get('locale')->addError(new FormError('notUniqueValue'));
            if ($form->isValid()) {
                $language = new Entity\Language();
                $language->setLocale($data['locale']);
                $language->setName($data['name']);
                $language->setAvailable($data['available']);
                $this->em->persist($language);
                $this->em->flush();

                $laRole = new Entity\Role();
                $laRole->setId('la/' . $language->getId());
                $this->em->persist($laRole);

                /** @var Entity\Repository\RoleRepository $roleRepository */
                $roleRepository = $this->em->getRepository(Entity\Role::EntityName());
                $lacRole = $roleRepository->find('lac');

                $subRole = new Entity\RoleSubrole();
                $subRole->setRole($lacRole);
                $subRole->setSubrole($laRole);
                $this->em->persist($subRole);
                $this->em->flush();

                return new RedirectResponse($this->router->ParseRoute('Language::Index'));
            }
        }
        $this->breadCrumb->AddItem("Languages", 'Language::Index');
        return $this->response(
            array('form' => $form->createView()),
            $this->breadCrumb
        );
    }

    /**
     * @Options(method={"GET", "POST"})
     * @param Request $request
     * @throws ResourceNotFoundException
     * @return Response
     */
    public function EditAction(Request $request)
    {
        if (!$this->security->hasRole("lac"))
            throw new \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException($request->getUri());
        /** @var Entity\Repository\LanguageRepository $languageRepository */
        $languageRepository = $this->em->getRepository(Entity\Language::EntityName());
        /** @var Entity\Language $language */
        $language = $languageRepository->find($request->get('id'));
        if ($language === null)
            throw new ResourceNotFoundException();
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->languageForm($this->router->ParseRoute('Language::Edit', array('id' => $request->get('id'))));
        $form->setData(array('locale' => $language->getLocale(), 'name' => $language->getName(), 'available' => $language->getAvailable()));
        if ($request->getMethod() === 'POST') {
            $form->handleRequest();
            $data = $form->getData();
            /** @var Entity\Language $otherLang */
            $otherLang = $languageRepository->findOneBy(array('locale' => $data['locale']));
            if ($language->getId() != $otherLang->getId())
                $form->get('locale')->addError(new FormError('notUniqueValue'));
            if ($form->isValid()) {
                /** @var Entity\Language $language */
                $language->setLocale($data['locale']);
                $language->setName($data['name']);
                $language->setAvailable($data['available']);
                $this->em->persist($language);
                $this->em->flush();
                return new RedirectResponse($this->router->ParseRoute('Language::Index'));
            }
        }
        $this->breadCrumb->AddItem("Languages", 'Language::Index');
        return $this->response(
            array('form' => $form->createView()),
            $this->breadCrumb
        );
    }

    /**
     * @Options(method={"GET"})
     * @param Request $request
     * @throws ResourceNotFoundException
     * @return Response
     */
    public function TranslateAllAction(Request $request)
    {
        if (!$this->security->hasRole("lac") && !$this->security->hasRole("lac_lang_" . $request->get('to')))
            throw new \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException($request->getUri());
        $params = $this->translate($request, 0);
        $params['route'] = 'Language::TranslateAll';
        return $this->response(
            $params,
            $this->breadCrumb,
            "Language/Translate.html.twig"
        );
    }

    /**
     * @Options(method={"GET"})
     * @param Request $request
     * @throws ResourceNotFoundException
     * @return Response
     */
    public function TranslateUntranslatedAction(Request $request)
    {
        if (!$this->security->hasRole("lac") && !$this->security->hasRole("lac_lang_" . $request->get('to')))
            throw new \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException($request->getUri());
        $params = $this->translate($request, 1);
        $params['route'] = 'Language::TranslateUntranslated';
        return $this->response(
            $params,
            $this->breadCrumb,
            "Language/Translate.html.twig"
        );
    }

    protected function translate(Request $request, $mode)
    {
        $to = $request->get('to');
        if (!$this->security->hasRole("lac") && !$this->security->hasRole("lac_lang_" . $to))
            throw new \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException($request->getUri());
        $from = $request->get('from');
        $domain = $request->get('domain');

        /** @var Entity\Repository\LanguageRepository $languageRepository */
        $languageRepository = $this->em->getRepository(Entity\Language::EntityName());
        /** @var Entity\Language[] $languages */
        $languages = $languageRepository->findAll();

        /** @var Entity\Repository\LanguageDomainRepository $languageRepository */
        $languageDomainRepository = $this->em->getRepository(Entity\LanguageDomain::EntityName());
        /** @var Entity\LanguageDomain[] $languageDomains */
        $languageDomains = $languageDomainRepository->findAll();

        $language = from($languages)->where(function (Entity\Language $l) use ($to) {
            return $l->getId() == $to;
        })->first();
        $otherLanguage = from($languages)->where(function (Entity\Language $l) use ($from) {
            return $l->getId() == $from;
        })->first();

        $query = "SELECT to, d, t1, t2 ";
        $query .= "FROM " . Entity\LanguageToken::EntityName() . " AS to ";
        $query .= "LEFT JOIN " . Entity\LanguageDomain::EntityName() . " AS d WITH (to.domain = d.id) ";
        $query .= "LEFT OUTER JOIN " . Entity\LanguageTranslation::EntityName() . " AS t1 WITH (to.id = t1.token AND t1.language = :l1) ";
        $query .= "LEFT OUTER JOIN " . Entity\LanguageTranslation::EntityName() . " AS t2 WITH (to.id = t2.token AND t2.language = :l2) ";
        $query .= "WHERE 1 = 1 ";
        if (strlen($domain))
            $query .= "AND d.id = :domain ";
        if ($mode == 1)
            $query .= "AND t1.translation IS NULL ";
        $userDomains = $this->allowedDomainsQueryString();
        if (!empty($userDomains))
            $query .= "AND d.id IN (".$userDomains.")";
        $query .= "ORDER BY d.id, to.token ASC";
        $query = $this->em->createQuery($query);
        $query->setParameter('l1', $to);
        $query->setParameter('l2', $from);
        if (strlen($domain))
            $query->setParameter('domain', $domain);

        $pagination = new Pagination($request);
        $paginator = $pagination->getPaginator($query);

        $tokens = array();
        if ($paginator->count() > 0) {
            $counter = -1;
            $iterator = $paginator->getIterator();
            foreach ($iterator as $item) {
                if ($item instanceof Entity\LanguageToken) {
                    $counter++;
                    $tokens[$counter]['token'] = $item;
                } else if ($item instanceof Entity\LanguageTranslation && $item->getLanguage()->getId() == $to) {
                    $tokens[$counter]['translation'] = $item;
                    if ($item->getLanguage()->getId() == $from)
                        $tokens[$counter]['otherTranslation'] = $item;
                } else if ($item instanceof Entity\LanguageTranslation && $item->getLanguage()->getId() == $from) {
                    $tokens[$counter]['otherTranslation'] = $item;
                }
            }
        }

        $this->breadCrumb->AddItem("Languages", 'Language::Index');
        return array('language' => $language, 'otherLanguage' => $otherLanguage, 'languages' => $languages, 'paginator' => $paginator, 'tokens' => $tokens, 'domains' => $languageDomains);
    }

    /**
     * @Options(method={"POST"}, xhr=true)
     * @param Request $request
     * @throws ResourceNotFoundException
     * @return Response
     */
    public function GetTranslationAction(Request $request)
    {
        $lang = $request->get('lang');

        if (!$this->security->hasRole("lac") && !$this->security->hasRole("lac_lang_" . $lang))
            throw new \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException($request->getUri());
        $token = $request->get('token');

        /** @var Entity\Repository\LanguageTranslationRepository $languageTranslationRepository */
        $languageTranslationRepository = $this->em->getRepository(Entity\LanguageTranslation::EntityName());
        /** @var Entity\LanguageTranslation $translation */
        $translation = $languageTranslationRepository->findOneBy(array('language' => $lang, 'token' => $token));

        $userDomains = $this->allowedDomains();
        /** @var Entity\Repository\LanguageTokenRepository $languageTokenRepository */
        $languageTokenRepository = $this->em->getRepository(Entity\LanguageToken::EntityName());
        /** @var Entity\LanguageToken $token */
        $token = $languageTokenRepository->find($token);
        if (!in_array($token->getDomain()->getId(), $userDomains))
            throw new \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException($request->getUri());
        if ($translation === null)
            return $this->responsePlain("");
        else
            return $this->responsePlain($translation->getTranslation());
    }

    /**
     * @Options(method={"POST"}, xhr=true)
     * @param Request $request
     * @throws ResourceNotFoundException
     * @return Response
     */
    public function SetTranslationAction(Request $request)
    {
        $language = $request->get('lang');
        if (!$this->security->hasRole("lac") && !$this->security->hasRole("lac_lang_" . $language))
            throw new \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException($request->getUri());

        $token = $request->get('token');
        $text = trim($request->get('text'));

        /** @var Entity\Repository\LanguageTranslationRepository $languageTranslationRepository */
        $languageTranslationRepository = $this->em->getRepository(Entity\LanguageTranslation::EntityName());
        /** @var Entity\LanguageTranslation $translation */
        $translation = $languageTranslationRepository->findOneBy(array('language' => $language, 'token' => $token));

        /** @var Entity\Repository\LanguageRepository $languageRepository */
        $languageRepository = $this->em->getRepository(Entity\Language::EntityName());
        /** @var Entity\Language $language */
        $language = $languageRepository->find($language);

        $userDomains = $this->allowedDomains();
        /** @var Entity\Repository\LanguageTokenRepository $languageTokenRepository */
        $languageTokenRepository = $this->em->getRepository(Entity\LanguageToken::EntityName());
        /** @var Entity\LanguageToken $token */
        $token = $languageTokenRepository->find($token);
        if (!in_array($token->getDomain()->getId(), $userDomains))
            throw new \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException($request->getUri());

        if (strlen($text) > 0){
            if ($translation === null) {
                $translation = new Entity\LanguageTranslation($language, $token, $text);
                $this->em->persist($translation);
            } else {
                $translation->setTranslation($text);
                $this->em->persist($translation);
            }
        } else if ($translation !== null) {
            $this->em->remove($translation);
        }
        $this->em->flush();
        return $this->responsePlain($text);
    }

    /**
     * @Options(method={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function TokenAddAction(Request $request)
    {
        if (!$this->security->hasRole("lac"))
            throw new \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException($request->getUri());
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->tokenForm($this->router->ParseRoute('Language::TokenAdd'));
        if ($request->getMethod() === 'POST') {
            $form->handleRequest();
            $data = $form->getData();
            /** @var Entity\Repository\LanguageTokenRepository $languageTokenRepository */
            $languageTokenRepository = $this->em->getRepository(Entity\LanguageToken::EntityName());
            /** @var Entity\LanguageToken $token */
            $token = $languageTokenRepository->findOneBy(array('token' => $data['token'], 'domain' => $data{'domain'}));
            /** @var Entity\Repository\LanguageDomainRepository $languageRepository */
            $languageDomainRepository = $this->em->getRepository(Entity\LanguageDomain::EntityName());
            /** @var Entity\LanguageDomain[] $ldomain */
            $domain = $languageDomainRepository->find($data['domain']);
            if ($token !== null)
                $form->get('token')->addError(new FormError('notUniqueValue'));
            if ($form->isValid()) {
                $token = new Entity\LanguageToken($domain, $data['token']);
                $this->em->persist($token);
                $this->em->flush();
                return new RedirectResponse($this->router->ParseRoute('Language::Index'));
            }
        }
        $this->breadCrumb->AddItem("Languages", 'Language::Index');
        return $this->response(
            array('form' => $form->createView()),
            $this->breadCrumb
        );
    }

    private function languageForm($route)
    {
        return $this->formFactory->createNamedBuilder('languageForm')
            ->setMethod('POST')
            ->setAction($route)
            ->add('locale', 'text', array(
                'required' => true,
                'label' => 'Locale',
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 5, 'max' => 5)),
                ),
            ))
            ->add('name', 'text', array(
                'required' => true,
                'label' => 'Name',
                'constraints' => array(
                    new NotBlank()
                ),
            ))
            ->add('available', 'checkbox', array(
                'label' => 'Available',
                'required' => false,
                'value' => 1
            ))
            ->add('add', 'submit', array(
                'label' => 'Save',
            ))
            ->getForm();
    }

    private function tokenForm($route)
    {
        /** @var Entity\Repository\LanguageDomainRepository $languageRepository */
        $languageDomainRepository = $this->em->getRepository(Entity\LanguageDomain::EntityName());
        /** @var Entity\LanguageDomain[] $languageDomains */
        $languageDomains = $languageDomainRepository->findAll();
        $domains = array();
        foreach ($languageDomains as $domain)
            $domains[$domain->getId()] = $domain->getId();
        return $this->formFactory->createNamedBuilder('tokenForm')
            ->setMethod('POST')
            ->setAction($route)
            ->add('domain', 'choice', array(
                'choices' => $domains,
                'required' => true,
                'label' => 'Domain'
            ))
            ->add('token', 'text', array(
                'required' => true,
                'label' => 'Token',
                'constraints' => array(
                    new NotBlank()
                ),
            ))
            ->add('add', 'submit', array(
                'label' => 'Save',
            ))
            ->getForm();
    }

    /** @return string[] */
    private function allowedDomains() {
        $domains = array();
        $roles = $this->security->getRolesString();
        /** @var Entity\Role $role */
        foreach ($roles as $role) {
            if (preg_match("/^lac_domain_/", $role)) {
                $domains[] = str_replace("lac_domain_", "", $role);
            }
        }

        if (empty($domains)) {
            /** @var Entity\Repository\LanguageDomainRepository $languageDomainRepository */
            $languageDomainRepository = $this->em->getRepository(Entity\LanguageDomain::EntityName());
            /** @var Entity\LanguageDomain[] $_domains */
            $_domains = $languageDomainRepository->findAll();
            foreach ($_domains as $d) {
                $domains[] = $d->getId();
            }
        }

        return $domains;
    }

    private function allowedDomainsQueryString() {
        $domains = $this->allowedDomains();
        foreach ($domains as $d => $domain)
            $domains[$d] = "'" . $domain . "'";
        return implode(',', $domains);
    }

}