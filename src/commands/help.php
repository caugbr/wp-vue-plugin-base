<?php

require_once dirname(__FILE__) . "/../utils.php";

class Help {
    
    use WpVuePluginBaseUtils;

    private $commands = [];
    private $name = "";
    private $path = "";
    public $expectedArgs = ["name"];
    public $description = "Show some explanation about the available commands";

    private $createHelp = "";

    public function __construct($args, $obj) {
        $this->localPath = $obj->localPath;
        $this->getCommands();
        $this->args2props($args);
        if (empty($this->name)) {
            $this->doIt();
        } else {
            $this->line($this->single($this->name), 2);
        }
    }

    private function doIt() {
        $this->line();
        $this->line("WP-Vue plugin base", 2);
        $this->line("------------------", 2);
        $this->line();
        $this->line("Available commands", 2);
        $this->line($this->createHelp, 2);
        foreach ($this->commands as $name => $obj) {
            $this->line($this->single($name), 2);
        }
    }

    public function getCommands() {
        $this->commands = [];
        $files = array_diff(scandir($this->localPath . '/src/commands'), array('.', '..', 'index.php'));
        foreach($files as $file) {
            $name = str_replace(".php", "", $file);
            $itm = [
                "fileName" => $file,
                "path" => $this->localPath . '/src/commands/' . $file,
                "className" => $this->toClassName($name)
            ];
            $this->commands[$name] = $itm;
        }
    }

    private function single($name) {
        $props = $this->getObj($name);
        $str = [strtoupper($name)];
        $str[] = str_repeat("-", strlen($name));
        $str[] = $props['description'];
        return "\n" . join("\n", $str) . "\n" . $this->sample($name, $props['expectedArgs']);
    }

    private function getObj($name) {
        $comm = $this->commands[$name];
        include_once $comm['path'];
        return get_class_vars($comm['className']);
    }

    private function sample($name, $args) {
        $args = array_map(function($e) { return "[{$e}]"; }, $args);
        return "\n    php wp-vue-plugin {$name} " . join(" ", $args) . "\n";
    }

}