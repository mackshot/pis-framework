<?php

namespace Pis\Framework\Twig;

use Pis\Framework\Twig\Parsers\TransPluralTokenParser;
use Pis\Framework\Twig\Visitors\TranslationDefaultDomainNodeVisitor;
use Symfony\Bridge\Twig\TokenParser\TransTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransChoiceTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransDefaultDomainTokenParser;

class TranslationExtension extends \Symfony\Bridge\Twig\Extension\TranslationExtension
{
    public function getNodeVisitors()
    {
        return array($this->getTranslationNodeVisitor(), new TranslationDefaultDomainNodeVisitor());
    }


    public function getTokenParsers()
    {
        return array(
            // {% trans %}Symfony is great!{% endtrans %}
            new TransTokenParser(),

            new TransPluralTokenParser(),

            // {% transchoice count %}
            //     {0} There is no apples|{1} There is one apple|]1,Inf] There is {{ count }} apples
            // {% endtranschoice %}
            new TransChoiceTokenParser(),

            // {% trans_default_domain "foobar" %}
            new TransDefaultDomainTokenParser(),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig\TwigFilter('trans', array($this, 'trans')),
            new \Twig\TwigFilter('transplural', array($this, 'transplural')),
            new \Twig\TwigFilter('transchoice', array($this, 'transchoice')),
        );
    }

    public function trans($message, array $arguments = array(), $domain = null, $locale = null, $count = null)
    {
        if (null === $domain) {
            $domain = 'general';
        }

        return $this->getTranslator()->transChoice($message, 1, $arguments, $domain, $locale);
    }

    public function transplural($message, array $arguments = array(), $domain = null, $locale = null)
    {
        if (null === $domain) {
            $domain = 'general';
        }
        $count = 2;

        return $this->getTranslator()->transChoice($message, 2, array_merge(array('%count%' => $count), $arguments), $domain, $locale);
    }

    public function transchoice($message, $count, array $arguments = array(), $domain = null, $locale = null)
    {
        if (null === $domain) {
            $domain = 'general';
        }

        return $this->getTranslator()->transChoice($message, $count, array_merge(array('%count%' => $count), $arguments), $domain, $locale);
    }


}
