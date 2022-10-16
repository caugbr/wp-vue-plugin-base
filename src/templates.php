<?php

trait WpVuePluginBaseTemplates {
    // show available templates
    public function show_templates() {
        $templates = $this->get_templates();
        foreach ($templates as $tpl) {
            $info = $this->extract_template_info($tpl);
            $def = $tpl == $this->defaultTemplate ? " (default)" : "";
            $this->line();
            $this->line("{$info['name']}{$def}");
            $this->line();
            $this->line("    " . $info['description']);
            $this->line("    ID: '{$tpl}'");
            if (empty($def)) {
                $this->line("    Use like this:\n");
                $this->line("        php wp-vue-plugin create --template={$tpl}\n");
            }
        }
    }

    // get info from a template file without instanciate the object
    public function extract_template_info($slug) {
        $arr = ['id' => '', 'name' => '', 'description' => ''];
        $code = file_get_contents("{$this->localPath}/templates/{$slug}/plugin-template.php");
        if (preg_match("/\btemplateId *= *[\"']([^\"']+)[\"']/", $code, $matches)) {
            $arr['id'] = $matches[1];
        }
        if (preg_match("/\btemplateName *= *[\"']([^\"']+)[\"']/", $code, $matches)) {
            $arr['name'] = $matches[1];
        }
        if (preg_match("/\btemplateDescription *= *[\"']([^\"']+)[\"']/", $code, $matches)) {
            $arr['description'] = $matches[1];
        }
        return $arr;
    }

    // return template slugs
    public function get_templates() {
        $dirs = $this->listDirectories("{$this->localPath}/templates");
        return array_map(function($e) { return basename($e); }, $dirs);
    }

    // includes necessary files and instanciate $this->template 
    public function get_template() {
        global $localPath;
        $localPath = $this->localPath;
        require_once "{$this->localPath}/src/template-base.php";
        require_once "{$this->localPath}/templates/{$this->templateName}/plugin-template.php";
        $this->template = new PluginTemplate($this->args, $this->flags);
        $this->template->set_info($this->template_values());
    }

    // get template info and set internal variables
    public function set_template_info() {
        $this->template->set_info($this->template_values());
        $this->base_files = $this->template->base_files();
        $this->base_directories = $this->template->base_directories();
        $this->replacement_files = $this->template->replacement_files();
        $this->replacement_terms = $this->template->replacement_terms();
    }

    // read a wp-vue-info.json file from the given plugin
    public function get_template_info($slug) {
        if ($this->wpCli()) {
            $dir = trim(shell_exec("wp plugin path"));
            $json = "{$dir}/{$slug}/wp-vue-info.json";
            if (file_exists($json)) {
                $info = json_decode(file_get_contents($json));
                $template = new stdClass();
                foreach ($info as $key => $value) {
                    $template->{$key} = $value;
                }
                return $template;
            }
            return [];
        }
    }

    // if $this->template is empty, fill it with the needed values
    public function check_template($slug) {
        if ($this->wpCli()) {
            if (empty($this->template)) {
                $this->template = $this->get_template_info($slug);
            }
        }
    }

    // prepare the values to be sent to the template class
    private function template_values() {
        return [
            "localPath" => $this->localPath,
            "pluginName" => $this->template->pluginName ?? '',
            "pluginSlug" => $this->template->pluginSlug ?? '',
            "pluginDirectory" => $this->template->pluginDirectory ?? '',
            "varName" => $this->template->varName ?? '',
            "className" => $this->template->className ?? '',
            "prefix" => $this->template->prefix ?? '',
            "postTypeName" => $this->template->postTypeName ?? '',
            "postTypeNamePlural" => $this->template->postTypeNamePlural ?? '',
            "postTypeSlug" => $this->template->postTypeSlug ?? '',
            "postTypeId" => $this->template->postTypeId ?? '',
            "wpLangDir" => $this->template->wpLangDir ?? ''
        ];
    }
}

?>