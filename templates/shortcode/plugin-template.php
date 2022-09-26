<?php

class PluginTemplate extends PluginData {

    public function __construct() {
        $this->templateId = "shortcode";
        $this->templateName = "Shortcode";
        $this->templateDescription = "Creates a plugin with a Vue app already functional, to use in site via shortcode.";
        $this->templateHtmlDescription = "<p>Use this template as a base to develop anything that can be displayed with a WP shortcode.</p> <p>This model has Vue integration only in frontend via shortcode. There is no integration in admin.</p>";
    }

    public function base_directories() {
        return ["langs"];
    }

    public function base_files() {
        return [
            'vue-plugin-base.php' => "{$this->pluginSlug}.php",
            'langs/prefix-pt_BR.mo' => "{$this->prefix}-pt_BR.mo",
            'langs/prefix-pt_BR.po' => "{$this->prefix}-pt_BR.po",
            'vue-app/*' => '*'
        ];
    }

    public function replacement_files() {
        return [
            "{$this->pluginDirectory}/langs/{$this->prefix}-pt_BR.po",
            "{$this->pluginDirectory}/vue-app/package.json"
        ];
    }

    public function replacement_terms() {
        return [
            "VuePluginBase"   => "className",
            "vue-plugin-slug" => "pluginSlug",
            "Plugin name"     => "pluginName",
            "vue_plugin_base" => "varName",
            "prefix"          => "prefix"
        ];
    }
}