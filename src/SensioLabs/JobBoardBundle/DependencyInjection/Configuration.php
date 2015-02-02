<?php

namespace SensioLabs\JobBoardBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sensiolabs_jobboard');

        $rootNode
            ->children()
                ->scalarNode('mailer_from_email')->isRequired()->end()
                ->scalarNode('mailer_from_name')->isRequired()->end()
                ->scalarNode('mailer_admin_email')->end()
            ->end();

        return $treeBuilder;
    }

}
