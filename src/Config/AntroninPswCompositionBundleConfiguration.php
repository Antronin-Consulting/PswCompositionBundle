<?php

declare(strict_types=1);
/**
 * File: \src\Config\NexonConfig.php
 * Author: Peter Nagy <peter@antronin.consulting>
 * -----
 */

namespace Antronin\PswCompositionBundle\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class AntroninPswCompositionBundleConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('psw_composition');

        $rootNode = $treeBuilder->getRootNode();
        $rootNode->children()
            ->arrayNode('length')
                ->canBeEnabled()
                ->children()
                    ->integerNode('min')
                        ->min(0)
                    ->end()
                    ->integerNode('max')
                        ->min(0)
                    ->end()
                ->end()
            ->end()
            ->arrayNode('contents', 'content')
                ->canBeEnabled()
                ->children()
                    ->arrayNode('uppercase')
                        ->canBeEnabled()
                        ->children()
                            ->integerNode('min')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->min(0)
                                ->defaultValue(1)
                            ->end()
                            ->stringNode('pattern')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('lowercase')
                        ->canBeEnabled()
                        ->children()
                            ->integerNode('min')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->min(1)
                                ->defaultValue(1)
                            ->end()
                            ->stringNode('pattern')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('number')
                        ->canBeEnabled()
                        ->children()
                            ->integerNode('min')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->min(1)
                                ->defaultValue(1)
                            ->end()
                            ->stringNode('pattern')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('special')
                        ->canBeEnabled()
                        ->children()
                            ->integerNode('min')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->min(1)
                                ->defaultValue(1)
                            ->end()
                            ->stringNode('pattern')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
        return $treeBuilder;
    }
}
