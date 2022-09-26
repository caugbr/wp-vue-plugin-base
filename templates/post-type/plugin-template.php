<?php

class PluginTemplate extends PluginData {

    public $post_type = true;

    public function __construct() {
        $this->templateId = "post-type";
        $this->templateName = "Post type";
        $this->templateDescription = "Creates a new post type and gives you two Vue apps running, one in admin, in a metabox to edit our post type and other to show this content in site, via shortcode.";
        $this->templateHtmlDescription = "<p>The generated plugin has a specific design and will be useful if you need to create a post type and display/edit it using Vue apps.</p> <p>In the administration part, there is a meta box when creating/editing a post (of the type we created) this opens a layer over the WP editor, with the same layout, so you can build your own editor for your content type.</p> <p>In the front side, this content will be displayed via shortcode, which is automatically included in the post when you create a post.</p>";
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
            "VuePluginBase"   => "className",
            "vue-plugin-slug" => "pluginSlug",
            "Plugin name"     => "pluginName",
            "VPB posts"       => "postTypeNamePlural",
            "VPB post"        => "postTypeName",
            "prefix_post"     => "postTypeId",
            "vue_plugin_base" => "varName",
            "prefix"          => "prefix"
        ];
    }
}