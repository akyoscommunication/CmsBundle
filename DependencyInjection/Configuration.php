<?php

namespace Akyos\CmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('cms_bundle');

        $treeBuilder->getRootNode()->children()
            // Rôles du CMS
            ->arrayNode('user_roles')->defaultValue(['Utilisateur' => 'ROLE_USER', 'Admin' => 'ROLE_ADMIN', 'Super Admin' => 'ROLE_SUPER_ADMIN', 'Akyos' => 'ROLE_AKYOS',])->scalarPrototype()->end()->end()->end();

        return $treeBuilder;
    }
}