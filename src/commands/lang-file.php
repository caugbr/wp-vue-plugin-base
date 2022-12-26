<?php

require_once dirname(__FILE__) . "/../utils.php";
require_once dirname(__FILE__) . "/../wp-cli.php";
require_once dirname(__FILE__) . "/../templates.php";

class LangFile {
    
    use WpVuePluginBaseUtils;
    use WpVuePluginBaseWpCli;
    use WpVuePluginBaseTemplates;

    public $template = NULL;
    public $slug = "";
    public $lang = "";
    public $langName = "";
    public $expectedArgs = ["slug", "lang", "langName"];
    public $description = "The command LANG-FILE generates a new translation file, with all translatable strings on it.\nThese are the strings sent to the translation functions t() and tl().\nThey will be extracted from your Vue files.";

    public function __construct($args, $obj) {
        $this->args2props($args);
        if (empty($this->slug)) {
            $this->line("ERROR: The plugin slug is required to command LANG-FILE.");
        } elseif (empty($this->lang)) {
            $this->line("ERROR: The language code is required to command LANG-FILE.");
        } elseif (!preg_match("/^[a-z]{2}(_[A-Z]{2})?$/", $this->lang)) {
            $this->line("ERROR: The language code does not match [a-z]{2}(_[A-Z]{2})? ('aa' or 'aa_AA')");
        } else {
            $this->doIt();
        }
    }

    public function doIt() {
        if ($this->wpCli()) {
            $this->check_template($this->slug);
            $pluginDir = str_replace("/{$this->slug}.php", "", $this->pluginPath($this->slug));
            if (!file_exists($pluginDir)) {
                $this->line();
                $this->line("The plugin {$this->slug} does not exist.");
                return;
            }
            if (empty($this->template->i18nDir) || !file_exists("{$pluginDir}/{$this->template->i18nDir}")) {
                $this->line();
                $this->line("The plugin {$this->slug} does not seem to use I18n JSON translations.");
                return;
            }
            $path = "{$pluginDir}/{$this->template->i18nDir}/{$this->lang}.json";
            $files = $this->listFiles("{$pluginDir}/{$this->template->appDir}/src/components");
            $viewFiles = $this->listFiles("{$pluginDir}/{$this->template->appDir}/src/views");
            $files = array_merge($files, $viewFiles);
            $code = ["language_name" => $this->langName];
            foreach ($files as $file) {
                $content = file_get_contents($file);
                preg_match_all("/\btl?\([\'\"]([^\'\"]+)[\'\"]\)/", $content, $matches);
                foreach ($matches[1] as $str) {
                    $code[$str] = "";
                }
            }
            $msg = "The file '{$path}' was created";
            if (file_exists($path)) {
                $original = json_decode(file_get_contents($path), true);
                foreach ($original as $str => $translation) {
                    if (isset($code[$str]) && empty($code[$str]) && !empty($translation)) {
                        $code[$str] = $translation;
                    }
                }
                $msg = "The file '{$path}' was updated";
            }
            file_put_contents($path, json_encode($code, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->line();
            $this->line($msg);
            return true;
        }
        return false;
    }
}