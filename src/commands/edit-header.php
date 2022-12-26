<?php

require_once dirname(__FILE__) . "/../utils.php";
require_once dirname(__FILE__) . "/../wp-cli.php";

class EditHeader {
    
    use WpVuePluginBaseUtils;
    use WpVuePluginBaseWpCli;

    public $slug = "";
    public $expectedArgs = ["slug"];
    public $description = "Edit the header values for the given plugin";

    public $headerInfo = [
        "Plugin URI" => "Plugin URI?",
        "Description" => "Plugin description?",
        "Version" => "Plugin version?",
        "Author" => "Author name?",
        "Author URI" => "Author URL?",
        "Text Domain" => "Text Domain?",
        "Domain Path" => "Text Domain path?",
        "License" => "License type?"
    ];
    public $userValues = [];

    public function __construct($args, $obj) {
        $this->args2props($args);
        if (empty($this->slug)) {
            $this->line("ERROR: The plugin slug is required to edit-header command");
        } else {
            $this->doIt();
        }
    }

    public function doIt() {
        if ($this->wpCli()) {
            $this->userValues = [];
            $info = $this->extract();
            $this->line("");
            foreach ($this->headerInfo as $key => $value) {
                $lv = empty($info[$key]) ? $value : "{$value} ({$info[$key]})";
                $this->line($lv);
                $this->userValues[$key] = readline();
                $this->userValues[$key] = empty($this->userValues[$key]) ? $info[$key] : $this->userValues[$key];
            }
            $this->set();
        }
    }

    public function set() {
        if ($this->wpCli()) {
            $pluginFile = $this->pluginPath($this->slug);
            if (!empty($pluginFile)) {
                $file = file_get_contents($pluginFile);
                $pos = strpos($file, " */") + 3;
                $code = substr($file, $pos, -1);
                $items = [];
                foreach ($this->userValues as $vname => $vval) {
                    $items[] = "{$vname}: {$vval}";
                }
                $nfile = "<?php\n\n/**\n * " . join("\n * ", $items) . "\n */{$code}";
                file_put_contents($pluginFile, $nfile);
                $this->line();
                $this->line("The header information was updated for plugin {$this->slug}");
            } else {
                $this->line("The plugin '{$this->slug}' does not exist");
            }
        }
    }

    public function extract() {
        if ($this->wpCli()) {
            $pluginsDir = trim(shell_exec("wp plugin path"));
            $pluginFile = "{$pluginsDir}/{$this->slug}/{$this->slug}.php";
            $file = file_get_contents($pluginFile);
            $parts1 = preg_split("/\/\*+/", $file);
            $parts2 = preg_split("/\*\//", $parts1[1]);
            $code = explode("\n", trim($parts2[0]));
            $info = [];
            foreach ($code as $line) {
                $nv = preg_split("/\s*:\s*/", preg_replace("/\s*\*\s/", "", $line));
                $info[$nv[0]] = trim($nv[1]);
            }
            return $info;
        }
    }
}