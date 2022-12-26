<?php

/**
 * Vue plugin base
 * ---------------
 * A starter code already integrated with Vue 2 to develop your WP plugin.
 * 
 * Author: Cau Guanabara
 */

require_once "templates.php";
require_once "utils.php";
require_once "wp-cli.php";
require_once "npm.php";
require_once "commands/index.php";

class WPVuePlugin {

    use WpVuePluginBaseTemplates;
    use WpVuePluginBaseUtils;
    use WpVuePluginBaseWpCli;
    use WpVuePluginBaseNpm;

    public $localPath;

    public $defaultTemplate = 'post-type'; // default template
    public $templateName = '';             // template to be used
    public $base_files;                    // files to be copied
    public $base_directories;              // directories to be created
    public $replacement_files;             // the files that will be processed
    public $replacement_terms;             // terms to be replaced
    public $template = NULL;               // instance of the template class

    public $args;
    public $flags;

    public function __construct($argv) {
        $this->args = $argv;
        $this->getFlags();

        $this->localPath = str_replace("/src", "", join("/", explode(DIRECTORY_SEPARATOR, dirname(__FILE__))));

        if (!empty($this->flags['template'])) {
            $templates = $this->get_templates();
            if (!in_array($this->flags['template'], $templates)) {
                $this->line();
                $this->line("The template '{$this->flags['template']}' does not exist");
                return;
            }
            $this->templateName = $this->flags['template'];
        } else {
            $this->templateName = $this->defaultTemplate;
        }
        $this->get_template();

        $this->getCommands();
    }

    public function getCommands() {
        $obj = clone $this;
        array_shift($obj->args);
        $this->commands = new pluginCommands($obj);
    }

    // read flags (--name=value) 
    // identifies the args that are flags and pull it out of the arguments array
    public function getFlags() {
        $this->flags = [];
        $newArgs = [];
        foreach ($this->args as $value) {
            if (preg_match("/--([a-z0-9_]+)=(.+)/i", $value, $matches)) {
                $this->flags[$matches[1]] = $matches[2];
            } else {
                $newArgs[] = $value;
            }
        }
        $this->args = $newArgs;
        return $this->flags;
    }

    // ask if user wants to pen the plugin file in Visual code
    public function askOpenCode() {
        if ($this->command_exists('code -v')) {
            if ($this->askYesNo("Open plugin file in Code? Y/N")) {
                shell_exec("code {$this->template->pluginDirectory}/{$this->template->pluginSlug}.php");
            }
        }
    }
}