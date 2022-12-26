<?php

require_once dirname(__FILE__) . "/../utils.php";
require_once dirname(__FILE__) . "/../wp-cli.php";
require_once dirname(__FILE__) . "/../templates.php";
require_once dirname(__FILE__) . "/../npm.php";

class Build {
    
    use WpVuePluginBaseUtils;
    use WpVuePluginBaseWpCli;
    use WpVuePluginBaseTemplates;
    use WpVuePluginBaseNpm;

    public $template;
    public $slug = "";
    public $expectedArgs = ["slug"];
    public $description = "The command BUILD generates the production package to publish your site";

    public function __construct($args, $obj) {
        // print_r($obj);
        // $this->slug = $obj->template->pluginSlug;
        $this->template = $obj->template;
        $this->args2props($args);
        if (empty($this->slug)) {
            $this->line("ERROR: The plugin slug is required to command BUILD.");
        } else {
            $this->doIt();
        }
    }

    public function doIt() {
        $this->build($this->slug);
    }
}