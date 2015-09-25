<?php

namespace Manuel\Bundle\UploadDataBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('upload_data');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode->children()
                ->scalarNode('files_dir')->defaultValue('%kernel.root_dir%/cache/uploads/data/')->end()

            ->arrayNode('templates')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('layout')->defaultValue('UploadDataBundle::base.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('ajax')->defaultValue('UploadDataBundle::base_ajax.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('ajax_modal')->defaultValue('UploadDataBundle::base_ajax_modal.html.twig')->cannotBeEmpty()->end()

                    ->scalarNode('new')->defaultValue('UploadDataBundle:Upload:new.html.twig')->cannotBeEmpty()->end()

                    ->scalarNode('list')->defaultValue('UploadDataBundle:Upload:list.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('list_table')->defaultValue('UploadDataBundle:Upload:list_table.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('list_table_headers')->defaultValue('UploadDataBundle:Upload:list_table_headers.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('list_table_rows')->defaultValue('UploadDataBundle:Upload:list_table_rows.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('list_javascript')->defaultValue('UploadDataBundle:Upload:list_javascript.html.twig')->cannotBeEmpty()->end()

                    ->scalarNode('show')->defaultValue('UploadDataBundle:Upload:show.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('show_item')->defaultValue('UploadDataBundle:Upload:show_item.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('show_table')->defaultValue('UploadDataBundle:Upload:show_table_content.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('show_small_info')->defaultValue('UploadDataBundle:Upload:show_small_info.html.twig')->cannotBeEmpty()->end()

                    ->scalarNode('read_select_columns')->defaultValue('UploadDataBundle:Read:select_columns.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('read_csv_separator')->defaultValue('UploadDataBundle:Read:Csv/separator.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('read_excel_preview_headers')->defaultValue('UploadDataBundle:Read:Excel/preview_headers.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('read_excel_select_row_headers')->defaultValue('UploadDataBundle:Read:Excel/select_row_headers.html.twig')->cannotBeEmpty()->end()

                    ->scalarNode('column')->defaultValue('UploadDataBundle:Default:column.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('column_action')->defaultValue('UploadDataBundle:Default:column_action.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('column_datetime')->defaultValue('UploadDataBundle:Default:column_datetime.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('column_link')->defaultValue('UploadDataBundle:Default:column_link.html.twig')->cannotBeEmpty()->end()
                    ->scalarNode('column_number')->defaultValue('UploadDataBundle:Default:column_number.html.twig')->cannotBeEmpty()->end()
                ->end()
            ->end()

            ->end();

        return $treeBuilder;
    }
}
