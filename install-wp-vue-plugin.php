<?php

/**
 * Vue plugin base
 * ---------------
 * A starter code already integrated with Vue 2 to develop your WP plugin.
 * 
 * Author: Cau Guanabara
 */

class InstallWPVuePlugin {
    
    public $pluginName;
    public $pluginSlug;
    public $pluginDirectory;
    public $varName;
    public $className;
    public $prefix;
    public $postTypeName;
    public $postTypeNamePlural;
    public $postTypeSlug;
    public $postTypeId;

    public $localPath;

    
    public $defaultTemplate = 'post-type'; // default model
    public $templateName = '';             // model to be used
    public $base_files;                    // files to be copied
    public $base_directories;              // directories to be created
    public $replacement_files;             // the files that will be processed
    public $replacement_terms;             // terms to be replaced
    public $template = NULL;

    public $args;
    public $flags;

    private $error = false;

    public function __construct($argv) {
        $this->args = $argv;
        $this->getFlags();

        $this->localPath = join("/", explode(DIRECTORY_SEPARATOR, dirname(__FILE__)));

        if (!empty($this->flags['template'])) {
            $templates = $this->get_templates();
            if (!in_array($this->flags['template'], $templates)) {
                $this->line();
                $this->line("The template '{$this->flags['template']}' does not exist");
                $this->show_templates();
                return;
            }
            $this->templateName = $this->flags['template'];
        } else {
            $this->templateName = $this->defaultTemplate;
        }
    }

    // create plugin
    public function create() {
        $this->line();
        $this->line("Generating plugin");
        $this->line("-----------------");
        $this->askInfo();
        $this->line();
        $this->line("Plugin Name: '{$this->pluginName}'");
        $this->line("Plugin Slug: {$this->pluginSlug}");
        $this->line("Prefix: {$this->prefix}_");
        $this->line("Textdomain: '{$this->prefix}'");
        if ($this->template->post_type) {
            $this->line("Post Type Name: '{$this->postTypeName}'");
            $this->line("Post Type NamePlural: '{$this->postTypeNamePlural}'");
            $this->line("Post Type Slug: {$this->postTypeSlug}");
        }
        $this->line("Plugin directory: {$this->pluginDirectory}");
        $this->line("Plugin file name: {$this->pluginSlug}.php");
        $this->line();
        $this->line("The PHP code will be like this:");
        $this->line();
        $this->line("    class {$this->className} { ... }");
        $this->line("    global \${$this->varName};");
        $this->line("    \${$this->varName} = new {$this->className}();");

        if (!$this->askYesNo("Review the generated values and continue if everything is ok.\nInstall now? (Y/N)")) {
            $this->line();
            $this->line("Cancelled by user.");
            return;
        }
        
        $this->copyFiles();
        if ($this->error) {
            return;
        }
        $this->replaceStrings();

        if ($this->askYesNo("Install packages now? (Y/N)")) {
            $this->installPackages();
        } else {
            $this->line();
            $this->line("Your app is not functional yet. You must install the project dependencies.");
            $this->line("To install packages manually, just type:");
            $this->line();
            $this->line("    php wp-vue-plugin install {$this->pluginSlug}");
        }

        $this->line();
        $this->line("The process is concluded, now we must start the development server,");
        $this->line("but before that we will try to activate the plugin '{$this->pluginName}' in WP.");

        if ($this->wpCli()) {
            $this->line();
            $this->line("Checking installed plugins...");
            $plugins = $this->getPlugins();
            if (!isset($plugins[$this->pluginSlug])) {
                $this->line();
                die("The plugin was not installed for some reason. Please clean all created files and run it again.");
            } else {
                $this->line();
                $this->line("✔ The plugin '{$this->pluginSlug}' was installed correctly");
            }
            if ($plugins[$this->pluginSlug]) {
                $this->line();
                $this->line("✔ The plugin '{$this->pluginSlug}' is already activated");
            } else {
                if ($this->askYesNo("Do you want to activate the plugin now? Y/N")) {
                    shell_exec("wp plugin activate {$this->pluginSlug}");
                    $plugins = $this->getPlugins();
                    if ($plugins[$this->pluginSlug]) {
                        $this->line();
                        $this->line("✔ The plugin '{$this->pluginSlug}' was activated");
                    }
                }
            }
            if ($plugins[$this->pluginSlug] && $this->template->post_type) {
                $this->line();
                $this->line("We can create a test post for you.");
                $this->line("To create it, type the post title and press Enter. Or just type Enter to ignore it.");
                $ptitle = readline();
                if (!empty($ptitle)) {
                    $call = "wp post create --post_type={$this->postTypeSlug} --post_title='{$ptitle}' --post_status=draft";
                    $output = shell_exec($call);
                    if (preg_match("/(\d+)/", $output, $matches)) {
                        $postId = $matches[1];
                        $this->line();
                        $this->line("✔ The post '{$ptitle}' was created");
                        $site = $this->getOption("siteurl");
                        $this->line();
                        $this->line("Edit your post (after the server is running):");
                        $this->line();
                        $this->line("    {$site}/wp-admin/post.php?post={$postId}&action=edit");
                    }
                } else {
                    $this->line("The test post was not created. Go to WP admin and create a post of type '{$this->postTypeName}'.");
                }
                $this->line("Edit some {$this->postTypeName}, find the box '{$this->postTypeName}' in sidebar and click the button 'Toggle Vue app' to see the admin app.");
                $this->line("The shortcode is auto inserted in all posts of type {$this->postTypeName}, so just visit some post to see the frontend app.");
            }
        } else {
            $this->line();
            $this->line("We could not activate the plugin, you must activate your plugin manually.");
        }

        if ($this->askYesNo("Run the development server here? Y/N")) {
            $this->line();
            $this->askOpenCode();
            $this->line();
            $this->line("The development server will be running here. Do not close this window. Ctrl + C to stop.");
            chdir("{$this->pluginDirectory}/{$this->template->app_dir}");
            shell_exec("npm run serve");
        } 
        else {
            $this->line();
            $this->line("To start the development server manually, just type:");
            $this->line();
            $this->line("    php wp-vue-plugin serve {$this->pluginSlug}");
            $this->askOpenCode();
        }
    }

    // ask for initial information
    public function askInfo() {
        $this->get_template();
        $this->line();
        if (empty($this->pluginName)) {
            while (empty($this->pluginName)) {
                $this->line("Plugin name?");
                $this->pluginName = readline();
            }
        }
        $this->pluginSlug = $this->toSlug($this->pluginName);
        $this->pluginDirectory =  str_replace(basename(dirname(__FILE__)), $this->pluginSlug, $this->localPath);
        $this->varName = $this->toSlug($this->pluginName, '_');
        $this->className = $this->toClassName($this->pluginName);
        $this->prefix = $this->toPrefix($this->pluginName);
        $this->postTypeName = empty($this->postTypeName) ? ucfirst($this->prefix) . " post" : $this->postTypeName;
        
        $this->line("Plugin slug? ({$this->pluginSlug})");
        $ps = readline();
        if (empty($ps)) {
            $ps = $this->pluginSlug;
        }
        $this->pluginSlug = $ps;
        
        $this->line("Plugin prefix/textdomain? ({$this->prefix})");
        $pref = readline();
        if (empty($pref)) {
            $pref = $this->prefix;
        }
        $this->prefix = $pref;
        
        if ($this->template->post_type) {
            $this->line("Post type name? ({$this->postTypeName})");
            $ptn = readline();
            if (empty($ptn)) {
                $ptn = $this->postTypeName;
            }
            $this->postTypeName = $ptn;
            $this->postTypeNamePlural = $this->postTypeName . "s";
            $this->postTypeSlug = $this->toSlug($this->postTypeName);
            $this->postTypeId = $this->toSlug($this->postTypeName, '_');
            
            $this->line("Post type name plural? ({$this->postTypeNamePlural})");
            $ptnp = readline();
            if (empty($ptnp)) {
                $ptnp = $this->postTypeNamePlural;
            }
            $this->postTypeNamePlural = $ptnp;
            
            $this->line("Post type slug? ({$this->postTypeSlug})");
            $pts = readline();
            if (empty($pts)) {
                $pts = $this->postTypeSlug;
            }
            $this->postTypeSlug = $pts;
        }

        $this->set_template_info();
    }

    // show available templates
    public function show_templates() {
        $templates = $this->get_templates();
        foreach ($templates as $tpl) {
            $info = $this->extract_template_info($tpl);
            $def = $tpl == $this->defaultTemplate ? " (default)" : "";
            $this->line();
            $this->line("{$info['name']}{$def}");
            $this->line();
            $this->line("    ID: '{$tpl}'");
            $this->line("    " . $info['description']);
        }
    }

    // get info from a template file without instanciate the object
    public function extract_template_info($slug) {
        $arr = ['name' => '', 'description' => ''];
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
        if (preg_match("/\btemplateHtmlDescription *= *[\"']([^\"']+)[\"']/", $code, $matches)) {
            $arr['htmlDescription'] = $matches[1];
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
        require_once "./plugin-data.php";
        require_once "{$this->localPath}/templates/{$this->templateName}/plugin-template.php";
        $this->template = new PluginTemplate();
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

    // read a info.json file from the given plugin
    public function get_template_info($slug) {
        $dir = trim(shell_exec("wp plugin path"));
        $json = "{$dir}/{$slug}/info.json";
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

    // if $this->template is empty, fill it with the needed values
    public function check_template($slug) {
        if ($this->wpCli()) {
            if (empty($this->template)) {
                $this->template = $this->get_template_info($slug);
            }
        }
    }

    // help
    public function help() {
        $this->line("\nWP-Vue plugin base\n------------------\n");
        $this->line("Available commands\n");
        $this->line("  TEMPLATES\n  ------");
        $this->line("  Use the command 'templates' to see the available plugin templates.\n");
        $this->line("      php wp-vue-plugin templates\n");
        $this->line("  CREATE\n  ------");
        $this->line("  The command 'create' generates a new Wordpress plugin integrated with Vue 2.\n");
        $this->line("      php wp-vue-plugin create\n");
        $this->line("  SERVE\n  -----");
        $this->line("  The command 'serve' starts the development server to the given plugin.\n");
        $this->line("      php wp-vue-plugin serve plugin-slug\n");
        $this->line("  BUILD\n  -----");
        $this->line("  The command 'build' generates the production package to publish your site.\n");
        $this->line("      php wp-vue-plugin build plugin-slug\n");
        $this->line("  LANG-FILE\n  ---------");
        $this->line("  The command 'lang-file' generates a new translation file, with all translatable strings on it.");
        $this->line("  These are the strings sent to the translation functions t() and tl().");
        $this->line("  They will be extracted from your Vue files.\n");
        $this->line("      php wp-vue-plugin lang-file plugin-slug lang_CODE\n");
    }

    // run 'npm install'
    public function install($slug) {
        $this->runNpm($slug, "install", "Installing packages...");
    }

    // run 'npm run serve'
    public function startServer($slug) {
        $this->runNpm($slug, "run serve", "The development server will be running here. Do not close this window. Ctrl + C to stop.");
    }

    // run 'npm run build'
    public function build($slug) {
        $this->runNpm($slug, "run build", "Generating the production package...");
    }

    // run generic npm commands
    public function runNpm($slug, $command, $message = '') {
        if ($this->wpCli()) {
            $this->check_template($slug);
            $dir = trim(shell_exec("wp plugin path"));
            if (file_exists("{$dir}/{$slug}/{$this->template->app_dir}")) {
                if (!empty($message)) {
                    $this->line();
                    $this->line($message);
                }
                chdir("{$dir}/{$slug}/{$this->template->app_dir}");
                shell_exec("npm {$command}");
            } else {
                $this->line();
                $this->line("The directory 'plugins/{$slug}/{$this->template->app_dir}' does not exists");
            }
        }
    }

    // delete entire plugin
    public function remove($slug) {
        if ($this->wpCli()) {
            $dir = trim(shell_exec("wp plugin path"));
            if (file_exists("{$dir}/{$slug}")) {
                shell_exec("rm -rf {$dir}/{$slug}");
            }
        }
    }

    // check if WP-CLI in present and warn if not
    public function wpCli($msg = true) {
        if (!$this->command_exists("wp")) {
            if ($msg) {
                $this->line();
                $this->line("WP-CLI is not installed");
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

    // adds the plugin header info
    public function pluginHeader() {
        $header = "/**\n * Plugin Name: {$this->pluginName}\n * Plugin URI: Plugin URL here\n";
        $header .= " * Description: Plugin description here\n * Version: 1.0\n * Author: Author name here\n";
        $header .= " * Author URI: Author URL here\n * Text Domain: prefix\n * Domain Path: /langs\n * License: Wordpress\n */";
        return $header;
    }

    // copy base files to new plugin location
    public function copyFiles() {
        if (file_exists($this->pluginDirectory)) {
            $this->line();
            $this->line("The directory {$this->pluginDirectory} already exists. Remove it before you can create a new one.");
            $this->error = true;
            return;
        }
        mkdir($this->pluginDirectory);
        foreach ($this->base_directories as $dir) {
            mkdir("{$this->pluginDirectory}/{$dir}");
        }

        foreach ($this->base_files as $path => $newName) {
            $pathParts = explode("/", $path);
            $fileName = array_pop($pathParts);
            $dir = "{$this->localPath}/templates/{$this->templateName}/plugin/";
            $path = join("/", $pathParts);
            if ($path) {
                $path .= "/";
            }
            if($fileName === '*') {
                $pathDest = str_replace("/" . array_pop($pathParts), "", $path);
                shell_exec("cp -r {$dir}{$path} {$this->pluginDirectory}/{$pathDest}");
                continue;
            }
            $original = $dir . $path . $fileName;
            $newFileName = preg_replace_callback("/%([a-z]+)%/i", function($matches) {
                return $this->{$matches[1]};
            }, $newName);
            $destiny = "{$this->pluginDirectory}/{$path}{$newFileName}";
            shell_exec("cp {$original} {$destiny}");
        }
        $info = json_encode(get_object_vars($this->template), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents("{$this->pluginDirectory}/info.json", $info);
        
        $this->line();
        $this->line("✔ Files copied");
    }

    // replace strins in plugin files
    public function replaceStrings() {
        $files = array_merge(["{$this->pluginDirectory}/{$this->pluginSlug}.php"], $this->replacement_files);

        $code = file_get_contents($files[0]);
        $code = preg_replace("/^<\?php/", "<?php\n" . $this->pluginHeader(), $code);
        file_put_contents($files[0], $code);

        foreach ($files as $file) {
            $code = file_get_contents($file);
            foreach ($this->replacement_terms as $str => $var) {
                $code = preg_replace("/" . preg_quote($str, "/") . "/", $this->{$var}, $code);
            }
            file_put_contents($file, $code);
        }
        
        $this->line();
        $this->line("✔ Replacements done");
    }

    // run 'npm install' in the creation process
    public function installPackages() {
        $this->line();
        $this->line("Navigate to {$this->pluginDirectory}/{$this->template->app_dir} and install packages.");
        $this->line("This process can take a while...");
        $this->line();
        chdir("{$this->pluginDirectory}/{$this->template->app_dir}");
        shell_exec("npm install");
        $this->line("✔ Installation completed");
    }

    // extract the translatable strings from vue files
    public function extractStrings($slug, $lang, $langName = '') {
        if ($this->wpCli()) {
            $this->check_template($slug);
            $pluginsDir = trim(shell_exec("wp plugin path"));
            $pluginDir = "{$pluginsDir}/{$slug}";
            if (!file_exists($pluginDir)) {
                $this->line();
                $this->line("The plugin {$slug} does not exist.");
                return;
            }
            if (empty($this->template->i18n_dir) || !file_exists("{$pluginDir}/{$this->template->i18n_dir}")) {
                $this->line();
                $this->line("The plugin {$slug} does not seem to use I18n translations.");
                return;
            }
            $path = "{$pluginDir}/{$this->template->i18n_dir}/{$lang}.json";
            $files = $this->listFiles("{$pluginDir}/{$this->template->app_dir}/src/components");
            $viewFiles = $this->listFiles("{$pluginDir}/{$this->template->app_dir}/src/views");
            $files = array_merge($files, $viewFiles);
            $code = ["language_name" => $langName];
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

    // slug from plugin name
    // "Plugin name" <=> plugin-name
    public function toSlug($str, $spacesTo = '-') {
        $str = $this->unaccent($str);
        $str = strtolower(trim($str));
        $str = preg_replace("/[^a-z0-9 ]/", " ", $str);
        $str = preg_replace("/ +/", $spacesTo, $str);
        return $str;
    }

    // write the class name from plugin name
    // "Plugin name" <=> PluginName
    public function toClassName($str) {
        $str = $this->unaccent($str);
        $str = preg_replace("/[^a-zA-Z ]+/", "", $str);
        $parts = preg_split("/\s+/", trim($str));
        $parts = array_map(function($word) { return ucfirst($word); }, $parts);
        return join("", $parts);
    }

    // prefix from plugin name
    // "Plugin name" <=> plu
    // "Three names or more" <=> tnom
    public function toPrefix($str) {
        $str = $this->unaccent($str);
        $prts = preg_split("/\s+/", trim($str));
        $parts = array_map(function($word) { return strtolower($word[0]); }, $prts);
        if (count($parts) < 3) {
            return strtolower(substr(join("", $prts), 0, 3));
        }
        return join("", $parts);
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

    // prepare the values to be sent to the template class
    private function template_values() {
        return [
            "pluginName" => $this->pluginName,
            "pluginSlug" => $this->pluginSlug,
            "pluginDirectory" => $this->pluginDirectory,
            "varName" => $this->varName,
            "className" => $this->className,
            "prefix" => $this->prefix,
            "postTypeName" => $this->postTypeName,
            "postTypeNamePlural" => $this->postTypeNamePlural,
            "postTypeSlug" => $this->postTypeSlug,
            "postTypeId" => $this->postTypeId
        ];
    }

    // ask if user wants to pen the plugin file in Visual code
    public function askOpenCode() {
        if ($this->command_exists('code -v')) {
            if ($this->askYesNo("Open plugin file in Code? Y/N")) {
                shell_exec("code {$this->pluginDirectory}/{$this->pluginSlug}.php");
            }
        }
    }

    // ask a question returning TRUE if user type 'yes' or 'y'
    public function askYesNo($question) {
        $this->line();
        $this->line($question);
        $response = readline();
        return (strtolower(substr($response, 0, 1)) == 'y');
    }

    // prints a line
    public function line($str = '') {
        print $str . PHP_EOL;
    }

    // remove accentuation from the given string
    public function unaccent($string) {
        if ( !preg_match('/[\x80-\xff]/', $string) ) {
            return $string;
        }
        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A', chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A', chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E', chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I', chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O', chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U', chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y', chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a', chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c', chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e', chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i', chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o', chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u', chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y', chr(195).chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a', chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a', chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c', chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c', chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd', chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e', chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e', chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g', chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g', chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h', chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i', chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i', chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i', chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j', chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L', chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L', chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(197).chr(129) => 'L', chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(197).chr(133) => 'N', chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(197).chr(137) => 'N', chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(197).chr(141) => 'o', chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(197).chr(145) => 'o', chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R', chr(197).chr(149) => 'r', chr(197).chr(150) => 'R', chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R', chr(197).chr(153) => 'r', chr(197).chr(154) => 'S', chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S', chr(197).chr(157) => 's', chr(197).chr(158) => 'S', chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's', chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't', chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u', chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u', chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u', chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w', chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z', chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z', chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
        );
        return strtr($string, $chars);
    }

    // test if some bash command exists
    private function command_exists($command) {
        $whereIsCommand = (PHP_OS == 'WINNT') ? 'where' : 'which';
        $process = proc_open("{$whereIsCommand} {$command}", [ ["pipe", "r"], ["pipe", "w"], ["pipe", "w"] ], $pipes);
        if ($process !== false) {
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            return ($stdout != '');
        }
        return false;
    }

    // list the files on the givem directory
    private function listFiles($directory) {
        $files = array(); 
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($rii as $file) {
            if (!$file->isDir()) { 
                $files[] = $file->getPathname(); 
            }
        }
        return $files;
    }

    // list the directories on the givem directory
    private function listDirectories($directory) {
        $dirs = glob("{$directory}/*", GLOB_ONLYDIR);
        return $dirs;
    }
}