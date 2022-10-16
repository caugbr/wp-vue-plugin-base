<?php

global $localPath;
require_once $localPath . "/src/utils.php";

abstract class TemplateBase {

    // Utilities
    use WpVuePluginBaseUtils;

    // Sent arguments
    private $args = [];
    // Defined flags (--flag=value)
    private $flags = [];

    // Template variables
    // Defines what will be asked to the user on installation,
    // than you can use these values to replace specific things in code
    public $templateVars = [
        'pluginName' => [
            'question' => 'Plugin name?',
            'default' => 'args[2]',
            'handler' => 'set_pluginName'
        ],
        'pluginSlug' => [
            'question' => 'Plugin slug?'
        ],
        'prefix' => [
            'question' => 'Plugin prefix/textdomain?'
        ],
        'shortcodeName' => [
            'question' => 'Shortcode Name?',
            'default' => '%prefix%_shortcode'
        ],
        'postTypeName' => [
            'question' => 'Post type name?',
            'default' => 'args[3]',
            'handler' => 'set_postTypeName'
        ],
        'postTypeNamePlural' => [
            'question' => 'Post type name plural?'
        ],
        'postTypeSlug' => [
            'question' => 'Post type slug?'
        ]
    ];

    // The template must define these values
    public $pluginDirectory = '';
    public $varName = '';
    public $className = '';

    // If TRUE the header information will be asked to user, otherwise the default
    // values will be used to all fields that does not have a default value.
    public $fillHeader = true;

    // Define as TRUE if the template needs to create a post type
    public $postType = false;
    // Define if the template app directory have another name or if it is in another place.
    // It is relative to the plugin directory, so you can use a sub directory like 'dir-name/vue-app'
    public $appDir = 'vue-app';
    // Path to folder i18n, that contains the JSON translation files.
    // Leave it empty if you don't want to use JSON translations.
    public $i18nDir = 'vue-app/src/i18n';
    public $wpLangDir = 'langs';

    public $devHost = 'http://127.0.0.1';
    public $devPort = '8080';

    public function devUrl() {
        return "{$this->devHost}:{$this->devPort}";
    }

    // Define arguments and flags - must be called on __construct($args, $flags)
    public function set_args($args, $flags) {
        $this->args = $args;
        $this->flags = $flags;
    }

    // Save all template variables as properties of this object
    public function set_info() {
        foreach ($this->templateVars as $key => $value) {
            if (isset($value['value'])) {
                $this->{$key} = $value['value'];
            }
        }
    }

    // Get the default value of a variable
    public function get_default($val) {
        if ($val && preg_match("/^args\[([1-9])\]$/", $val, $matches)) {
            $ind = (int) $matches[1];
            if (!empty($this->args[$ind])) {
                return $this->args[$ind];
            }
            return '';
        }
        if ($val && preg_match("/%([a-z]+)%/i", $val)) {
            $_this = $this;
            return preg_replace_callback("/%([a-z]+)%/i", function($m) use(&$_this) {
                return $_this->templateVars[$m[1]]['value'];
            }, $val);
        }
        if (isset($this->templateVars[$val])) {
            if (!empty($this->templateVars[$val]['value'])) {
                return $this->templateVars[$val]['value'];
            }
            return '';
        }
        if (isset($this->{$val})) {
            if (!empty($this->{$val})) {
                return $this->{$val};
            }
            return '';
        }
        return $val;
    }

    // Ask for initial information
    public function ask_info() {
        $this->line();
        foreach ($this->templateVars as $vname => $vval) {
            if (!$this->postType && false !== strpos($vname, 'postType')) {
                continue;
            }
            $def = $this->get_default($vval['default'] ?? $vname);
            $this->templateVars[$vname]['value'] = $def;
            $this->line($vval['question'] . ($def ? " ({$def})" : ""));
            $answer = readline();
            if (empty($answer)) {
                if ($def) {
                    $answer = $def;
                } 
                elseif (!isset($vval['required']) || !!$vval['required']) {
                    while (empty($answer)) {
                        $this->line($vval['question']);
                        $answer = readline();
                    }
                }
            }
            $this->templateVars[$vname]['value'] = $answer;
            if (!empty($vval['handler']) && method_exists($this, $vval['handler'])) {
                $this->{$vval['handler']}($answer);
            }
        }
        $this->set_info();
    }

    // Display the defined variables to the user
    public function display_info($headerInfo = []) {
        foreach ($this->templateVars as $vname => $vval) {
            if (!$this->postType && false !== strpos($vname, 'postType')) {
                continue;
            }
            $this->line("    {$vname}: {$this->$vname}");
        }
        if (count($headerInfo)) {
            $this->line();
            $this->line("Plugin header");
            $this->line();
            foreach ($headerInfo as $vname => $vval) {
                $this->line("    {$vname}: {$vval['value']}");
            }
        }
    }

    // Hook - defines default values for all variables that are variations of the given plugin name
    public function set_pluginName($value) {
        if (empty($value)) {
            $value = $this->templateVars['pluginName']['value'] ?? '';
        }
        $this->templateVars['pluginSlug']['value'] = $this->toSlug($value);
        $this->templateVars['prefix']['value'] = $this->toPrefix($value);
        $this->templateVars['postTypeName']['value'] = ucfirst($this->toPrefix($value)) . " post";

        global $localPath;
        $this->pluginDirectory = str_replace('/' . basename($localPath), '/' . $this->toSlug($value), $localPath);
        $this->varName = $this->toSlug($value, '_');
        $this->className = $this->toClassName($value);
    }
    
    // Hook - defines default values for all variables that are variations of the given post type name
    public function set_postTypeName($value) {
        if (empty($value)) {
            $value = $this->templateVars['postTypeName']['value'] ?? '';
        }
        $this->templateVars['postTypeName']['value'] = $value;
        $this->templateVars['postTypeNamePlural']['value'] = $value . "s";
        $this->templateVars['postTypeSlug']['value'] = $this->toSlug($value);

        $this->postTypeId = $this->toSlug($value, '_');
    }

    // Should return the directories that must be created (array)
    abstract protected function base_directories();
    // Should return the files / directories that must be copied (array)
    abstract protected function base_files();
    // Should return the files that will have it's content replaced (array)
    abstract protected function replacement_files();
    // Should return the terms to replace and it's replacements (array)
    abstract protected function replacement_terms();
}