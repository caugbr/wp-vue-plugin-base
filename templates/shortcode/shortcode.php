<?php

class Shortcode extends TemplateBase {

    public $templateId = "shortcode";
    public $templateName = "Shortcode";
    public $templateDescription = "Creates a plugin with a Vue app already functional, to use in site via shortcode.";

    public function __construct($args, $flags) {
        $this->set_args($args, $flags);
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
            "VuePluginBase"         => "className",
            "vue-plugin-slug"       => "pluginSlug",
            "Plugin name"           => "pluginName",
            "vue_plugin_base"       => "varName",
            "prefix"                => "prefix",
            "http://localhost:8080" => "devUrl"
        ];
    }
}