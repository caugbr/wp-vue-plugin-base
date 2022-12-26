<?php

trait WpVuePluginBaseWpCli {

    // check if WP-CLI in present and warn if not
    public function wpCli($msg = true) {
        if (!$this->command_exists("wp")) {
            if ($msg) {
                $this->line();
                $this->line("WP-CLI is not installed!");
            }
            return false;
        }
        return true;
    }

    // get a WP option using WP-CLI
    public function getOption($optName) {
        if ($this->wpCli()) {
            return trim(shell_exec("wp option get {$optName}"));
        }
        return false;
    }

    // return the path to the main file of the given plugin
    public function pluginPath($slug) {
        if ($this->wpCli()) {
            $pluginsDir = trim(shell_exec("wp plugin path"));
            $pluginFile = "{$pluginsDir}/{$this->slug}/{$this->slug}.php";
            return file_exists($pluginFile) ? $pluginFile : false;
        }
        return false;
    }

    // get a list of plugins using WP-CLI
    public function getPlugins() {
        if ($this->wpCli()) {
            $plugs = explode("\n", shell_exec("wp plugin list"));
            $plugins = [];
            foreach($plugs as $i => $pline) {
                if ($i > 0) {
                    $parts = preg_split("/[\s\t]+/", trim($pline));
                    if (isset($parts[1])) {
                        $plugins[$parts[0]] = ($parts[1] == 'active');
                    }
                }
            }
            return $plugins;
        }
        return false;
    }
}

?>