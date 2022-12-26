<?php

require_once dirname(__FILE__) . "/../utils.php";
require_once dirname(__FILE__) . "/../wp-cli.php";
require_once dirname(__FILE__) . "/../templates.php";
require_once dirname(__FILE__) . "/../Npm.php";

class Serve {
    
    use WpVuePluginBaseUtils;
    use WpVuePluginBaseWpCli;
    use WpVuePluginBaseTemplates;
    use WpVuePluginBaseNpm;

    public $slug = "";
    public $expectedArgs = ["slug"];
    public $description = "The command SERVE starts the development server for the given plugin";

    public function __construct($args, $obj) {
        $this->args2props($args);
        if (empty($this->slug)) {
            $this->line("ERROR: The plugin slug is required to command SERVE.");
        } else {
            $this->doIt();
        }
    }

    public function doIt() {
        $this->startServer($this->slug);
    }
}