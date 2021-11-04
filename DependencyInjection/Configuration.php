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
        $treeBuilder = new TreeBuilder('upload_data');
        $rootNode    = $treeBuilder->getRootNode();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode->children()
                ->scalarNode('files_dir')->defaultValue('%kernel.cache_dir%/../uploads/data/')->end()
                ->scalarNode('uploaded_file_helper')->defaultValue('upload_data.file_helper.local')->cannotBeEmpty()->end()
                ->scalarNode('debugging_role')->defaultValue('ROLE_UPLOAD_DEBUGGING')->cannotBeEmpty()->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('layout')->defaultValue('@UploadData/base.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('ajax')->defaultValue('@UploadData/base_ajax.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('ajax_modal')->defaultValue('@UploadData/base_ajax_modal.html.twig')->cannotBeEmpty()->end()

                        ->scalarNode('new')->defaultValue('@UploadData/Upload/new.html.twig')->cannotBeEmpty()->end()

                        ->scalarNode('list')->defaultValue('@UploadData/Upload/list.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('list_table')->defaultValue('@UploadData/Upload/list_table.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('list_table_headers')->defaultValue('@UploadData/Upload/list_table_headers.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('list_table_rows')->defaultValue('@UploadData/Upload/list_table_rows.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('list_javascript')->defaultValue('@UploadData/Upload/list_javascript.html.twig')->cannotBeEmpty()->end()

                        ->scalarNode('show')->defaultValue('@UploadData/Upload/show.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('show_item')->defaultValue('@UploadData/Upload/show_item.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('show_table')->defaultValue('@UploadData/Upload/show_table_content.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('show_small_info')->defaultValue('@UploadData/Upload/show_small_info.html.twig')->cannotBeEmpty()->end()

                        ->scalarNode('read_select_columns')->defaultValue('@UploadData/Read/select_columns.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('read_csv_separator')->defaultValue('@UploadData/Read/Csv/separator.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('read_excel_preview_headers')->defaultValue('@UploadData/Read/Excel/preview_headers.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('read_excel_select_row_headers')->defaultValue('@UploadData/Read/Excel/select_row_headers.html.twig')->cannotBeEmpty()->end()

                        ->scalarNode('column')->defaultValue('@UploadData/Default/column.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('column_action')->defaultValue('@UploadData/Default/column_action.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('column_datetime')->defaultValue('@UploadData/Default/column_datetime.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('column_link')->defaultValue('@UploadData/Default/column_link.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('column_number')->defaultValue('@UploadData/Default/column_number.html.twig')->cannotBeEmpty()->end()
                    ->end()
                ->end()

            ->end();

        return $treeBuilder;
    }
}
