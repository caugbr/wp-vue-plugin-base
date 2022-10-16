<?php

class PluginTemplate extends TemplateBase {

    public $postType = true;
    public $devPort = '8081';

    public function __construct($args, $flags) {
        $this->set_args($args, $flags);

        $this->templateVars['postTypeDescription'] = [
            "question" => "Post type description?",
            "required" => false
        ];
        $this->templateId = "post-type";
        $this->templateName = "Post type";
        $this->templateDescription = "Creates a new post type and gives you two Vue apps running, one in admin, in a metabox to edit your post type content and other to show this content in site, via shortcode.";
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
            "{$this->pluginDirectory}/vue-app/src/components/Wp/api.js",
            "{$this->pluginDirectory}/vue-app/package.json"
        ];
    }

    public function replacement_terms() {
        return [
            "VuePluginBase"                => "className",
            "vue-plugin-slug"              => "pluginSlug",
            "Plugin name"                  => "pluginName",
            "VPB posts"                    => "postTypeNamePlural",
            "VPB post"                     => "postTypeName",
            "prefix_post"                  => "postTypeId",
            "vue_plugin_base"              => "varName",
            "prefix_shortcode"             => "shortcodeName",
            "prefix"                       => "prefix",
            "WP-Vue plugin base post type" => "postTypeDescription",
            "http://localhost:8080"        => "devUrl"
        ];
    }
}