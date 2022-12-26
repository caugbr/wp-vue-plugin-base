<?php

require_once dirname(__FILE__) . "/../utils.php";
require_once dirname(__FILE__) . "/../templates.php";

class Templates {
    
    use WpVuePluginBaseTemplates;
    use WpVuePluginBaseUtils;

    public $localPath = "";
    public $defaultTemplate = "post-type";
    public $expectedArgs = [];
    public $description = "Use the command TEMPLATES to see the available plugin templates";

    public function __construct($args, $obj) {
        $this->localPath = $obj->localPath;
        $this->doIt();
    }

    public function doIt() {
        $templates = $this->get_templates();
        foreach ($templates as $tpl) {
            $info = $this->extractTemplateInfo($tpl);
            $def = $tpl == $this->defaultTemplate ? " (default)" : "";
            $this->line();
            $this->line("{$info['templateName']}{$def}", 2);
            $this->line();
            $this->line($info['templateDescription'], 4);
            $this->line("ID: '{$tpl}'", 4);
            if (empty($def)) {
                $this->line("Use like this:", 4);
                $this->line();
                $this->line("php wp-vue-plugin create --template={$tpl}\n", 8);
            }
        }
    }

    // get info from a template file without instanciate the class
    public function extractTemplateInfo($slug) {
        include_once "{$this->localPath}/templates/{$slug}/{$slug}.php";
        $cls = $this->toClassName($slug);
        $info = get_class_vars($cls);
        return $info;
    }
}