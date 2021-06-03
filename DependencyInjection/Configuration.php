<?php


namespace Parad0xe\Bundle\FilterBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('filter');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('store_options')
                    ->children()
                        ->scalarNode("session_key")->isRequired()->defaultValue("__flts_sessk")->cannotBeEmpty()->end()
                        ->scalarNode("request_key")->isRequired()->defaultValue("__flts_rqtsk")->cannotBeEmpty()->end()
                        ->scalarNode("cleaner_key")->isRequired()->defaultValue("__flts_clnrk")->cannotBeEmpty()->end()
                        ->scalarNode("cached")->isRequired()->defaultValue(true)->cannotBeEmpty()->end()
                        ->scalarNode("method")->isRequired()->defaultValue("post")->cannotBeEmpty()->end()
                        ->scalarNode("auto_clean_timeout")->isRequired()->defaultValue(300)->cannotBeEmpty()->end()
                    ->end()
                ->end()

                ->arrayNode('class_options')
                    ->children()
                        ->scalarNode("available_methods_prefix")->isRequired()->defaultValue("pub_")->cannotBeEmpty()->end()
                    ->end()
                ->end()

                ->arrayNode('view_options')
                    ->children()
                        ->scalarNode("id_length")->isRequired()->defaultValue(4)->cannotBeEmpty()->end()
                        ->scalarNode("custom_case_separator")->isRequired()->defaultValue("-")->cannotBeEmpty()->end()
                        ->scalarNode("default_render_pattern")->isRequired()->defaultValue("{id}<{method}>")->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
