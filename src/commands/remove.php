<?php

require_once dirname(__FILE__) . "/../utils.php";
require_once dirname(__FILE__) . "/../wp-cli.php";

class Remove {
    
    use WpVuePluginBaseUtils;
    use WpVuePluginBaseWpCli;

    public $slug = "";
    public $expectedArgs = ["slug"];
    public $description = "Remove a entire plugin permanently";

    public function __construct($args, $obj) {
        $this->args2props($args);
        if (empty($this->slug)) {
            $this->line("ERROR: The plugin slug is required to command REMOVE.");
        } else {
            if ($this->askYesNo("This action will remove the entire plugin permanently. Continue? (Y/N)")) {
                $this->doIt();
            }
        }
    }

    public function doIt() {
        if ($this->wpCli()) {
            $pluginDir = str_replace("/{$this->slug}.php", "", $this->pluginPath($this->slug));
            if (!empty($pluginDir)) {
                shell_exec("rm -rf {$pluginDir}");
            } else {
                $this->line();
                $this->line("ERROR: The plugin '{$this->slug}' does not exist");
            }
        }
    }
}