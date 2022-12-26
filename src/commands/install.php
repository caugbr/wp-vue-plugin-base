<?php

require_once dirname(__FILE__) . "/../utils.php";
require_once dirname(__FILE__) . "/../wp-cli.php";
require_once dirname(__FILE__) . "/../templates.php";

class Install {
    
    use WpVuePluginBaseUtils;
    use WpVuePluginBaseWpCli;
    use WpVuePluginBaseTemplates;

    public $slug = "";
    public $expectedArgs = ["slug"];
    public $description = "Install NPM packages for the given plugin";

    public function __construct($args, $obj) {
        $this->args2props($args);
        if (empty($this->slug)) {
            $this->line("ERROR: The plugin slug is required to command INSTALL.");
        } else {
            $this->doIt();
        }
    }

    public function doIt() {
        if ($this->wpCli()) {
            $info = $this->get_template_info($this->slug);
            $pluginDir = str_replace("/{$this->slug}.php", "/{$info->appDir}", $this->pluginPath($this->slug));
            if (file_exists($pluginDir)) {
                chdir($pluginDir);
                shell_exec("npm install");
            } else {
                $this->line();
                $this->line("ERROR: The plugin '{$this->slug}' does not exist");
            }
        }
    }
}