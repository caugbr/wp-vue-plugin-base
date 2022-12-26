<?php

require_once dirname(__FILE__) . "/../utils.php";

class pluginCommands {
    
    use WpVuePluginBaseUtils;

    private $instance = null;
    private $path = '';
    private $name = '';
    private $args = [];
    private $flags = [];
    public $commands = [];
    public $called = false;
    public $isCommand = false;

    public function __construct($obj) {
        $this->instance = $obj;
        $this->localPath = $obj->localPath;
        $this->name = array_shift($obj->args);
        $this->args = $obj->args;
        $this->flags = $obj->flags;
        $this->getCommands();
        if ($this->commandExists()) {
            $this->isCommand = true;
            $this->callCommand();
        }
    }

    public function getCommands() {
        $this->commands = [];
        $files = array_diff(scandir($this->localPath . '/src/commands'), array('.', '..', 'index.php'));
        foreach($files as $file) {
            $name = str_replace(".php", "", $file);
            $itm = [
                "path" => $this->localPath . '/src/commands/' . $file,
                "className" => $this->toClassName($name)
            ];
            $this->commands[$name] = $itm;
        }
    }

    public function commandExists($name = '') {
        if (empty($name)) {
            $name = $this->name;
        }
        return isset($this->commands[$name]);
    }

    public function callCommand($name = '') {
        if (empty($name)) {
            $name = $this->name;
        }
        if ($this->commandExists($name)) {
            require_once $this->commands[$name]["path"];
            new $this->commands[$name]["className"]($this->args, $this->instance);
            // new $this->commands[$name]["className"]($this->args, $this->flags, $this->localPath);
            $this->called = true;
        }
    }
}