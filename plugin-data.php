<?php

abstract class PluginData {
    public $pluginName = "";
    public $templateDescription = "";
    public $pluginSlug = "";
    public $pluginDirectory = "";
    public $varName = "";
    public $className = "";
    public $prefix = "";
    public $postTypeName = "";
    public $postTypeNamePlural = "";
    public $postTypeSlug = "";
    public $postTypeId = "";

    // Define as TRUE if the template needs to create a post type
    public $post_type = false;
    // Define if the template app directory have another name or if it is in another place.
    // It is relative to the plugin directory, so you can use a sub directory like 'dir-name/vue-app'
    public $app_dir = 'vue-app';
    // Path to folder i18n, that contains the JSON translation files.
    // Leave it empty if you don't want to use JSON translations.
    public $i18n_dir = 'vue-app/src/i18n';

    // Should return the directories that must be created (array)
    abstract protected function base_directories();
    // Should return the files / directories that must be copied (array)
    abstract protected function base_files();
    // Should return the files that will have it's content replaced (array)
    abstract protected function replacement_files();
    // Should return the terms to replace and it's replacements (array)
    abstract protected function replacement_terms();

    public function set_info($info) {
        foreach ($info as $key => $value) {
            $this->{$key} = $value;
        }
    }
}