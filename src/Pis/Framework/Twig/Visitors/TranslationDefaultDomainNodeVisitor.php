<?php

namespace Pis\Framework\Twig\Visitors;

use Symfony\Bridge\Twig\Node\TransNode;
use Symfony\Bridge\Twig\Node\TransDefaultDomainNode;
use Symfony\Bridge\Twig\NodeVisitor\Scope;

class TranslationDefaultDomainNodeVisitor implements \Twig\NodeVisitor\NodeVisitorInterface
{

    /**
     * @var Scope
     */
    private $scope;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->scope = new Scope();
    }

    /**
     * {@inheritdoc}
     */
    public function enterNode(\Twig\Node\Node $node, \Twig\Environment $env)
    {
        if ($node instanceof \Twig\Node\BlockNode || $node instanceof \Twig\Node\ModuleNode) {
            $this->scope = $this->scope->enter();
        }

        if ($node instanceof TransDefaultDomainNode) {
            if ($node->getNode('expr') instanceof \Twig\Node\Expression\ConstantExpression) {
                $this->scope->set('domain', $node->getNode('expr'));

                return $node;
            } else {
                $var = $env->getParser()->getVarName();
                $name = new \Twig\Node\Expression\AssignNameExpression($var, $node->getLine());
                $this->scope->set('domain', new \Twig\Node\Expression\NameExpression($var, $node->getLine()));

                return new \Twig\Node\SetNode(false, new \Twig\Node\Node(array($name)), new \Twig\Node\Node(array($node->getNode('expr'))), $node->getLine());
            }
        }

        if (!$this->scope->has('domain')) {
            return $node;
        }

        if ($node instanceof \Twig\Node\Expression\Filter\DefaultFilter && in_array($node->getNode('filter')->getAttribute('value'), array('trans', 'transplural', 'transchoice'))) {
            $ind = 'trans' === $node->getNode('filter')->getAttribute('value') ? 1 : 2;
            $arguments = $node->getNode('arguments');
            if (!$arguments->hasNode($ind)) {
                if (!$arguments->hasNode($ind - 1)) {
                    $arguments->setNode($ind - 1, new \Twig\Node\Expression\ArrayExpression(array(), $node->getLine()));
                }

                $arguments->setNode($ind, $this->scope->get('domain'));
            }
        } elseif ($node instanceof TransNode) {
            if (null === $node->getNode('domain')) {
                $node->setNode('domain', $this->scope->get('domain'));
            }
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(\Twig\NodeInterface $node, \Twig\Environment $env)
    {
        if ($node instanceof TransDefaultDomainNode) {
            return false;
        }

        if ($node instanceof \Twig\Node\BlockNode || $node instanceof \Twig\Node\ModuleNode) {
            $this->scope = $this->scope->leave();
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return -10;
    }
}
