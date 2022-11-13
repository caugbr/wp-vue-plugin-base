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

class WPVuePlugin {

    use WpVuePluginBaseTemplates;
    use WpVuePluginBaseUtils;
    use WpVuePluginBaseWpCli;
    use WpVuePluginBaseNpm;

    public $localPath;

    public $defaultTemplate = 'post-type'; // default model
    public $templateName = '';             // model to be used
    public $base_files;                    // files to be copied
    public $base_directories;              // directories to be created
    public $replacement_files;             // the files that will be processed
    public $replacement_terms;             // terms to be replaced
    public $template = NULL;               // holds the instance of templates class

    public $args;
    public $flags;

    public $headerInfo = [
        "Plugin URI" => [
            "question" => "Plugin URI?",
            "default" => "Plugin URL here"
        ],
        "Description" => [
            "question" => "Plugin description",
            "default" => "Plugin description here"
        ],
        "Version" => [
            "question" => "Plugin version?",
            "default" => "1.0"
        ],
        "Author" => [
            "question" => "Author name?",
            "default" => "Author name here"
        ],
        "Author URI" => [
            "question" => "Author URL?",
            "default" => "Author URL here"
        ],
        "Text Domain" => [
            "value" => "%prefix%"
        ],
        "Domain Path" => [
            "question" => "Domain Path?",
            "default" => "%wpLangDir%"
        ],
        "License" => [
            "question" => "License?",
            "default" => "Wordpress"
        ]
    ];

    public function __construct($argv) {
        $this->args = $argv;
        $this->getFlags();

        $this->localPath = str_replace("/src", "", join("/", explode(DIRECTORY_SEPARATOR, dirname(__FILE__))));

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
        $this->get_template();
    }

    // help
    public function help() {
        $this->line();
        $this->line("WP-Vue plugin base");
        $this->line("------------------");
        $this->line("\nAvailable commands\n");
        $this->line("  TEMPLATES");
        $this->line("  ---------");
        $this->line("  Use the command 'templates' to see the available plugin templates.\n");
        $this->line("      php wp-vue-plugin templates\n");
        $this->line("  CREATE");
        $this->line("  ------");
        $this->line("  The command 'create' generates a new Wordpress plugin integrated with Vue 2.\n");
        $this->line("      php wp-vue-plugin create\n");
        $this->line("  SERVE");
        $this->line("  -----");
        $this->line("  The command 'serve' starts the development server to the given plugin.\n");
        $this->line("      php wp-vue-plugin serve plugin-slug\n");
        $this->line("  INSTALL");
        $this->line("  -------");
        $this->line("  Install (or reinstall) the project packages.\n");
        $this->line("      php wp-vue-plugin install plugin-slug\n");
        $this->line("  BUILD");
        $this->line("  -----");
        $this->line("  The command 'build' generates the production package to publish your site.\n");
        $this->line("      php wp-vue-plugin build plugin-slug\n");
        $this->line("  LANG-FILE");
        $this->line("  ---------");
        $this->line("  The command 'lang-file' generates a new translation file, with all translatable strings on it.");
        $this->line("  These are the strings sent to the translation functions t() and tl().");
        $this->line("  They will be extracted from your Vue files.\n");
        $this->line("      php wp-vue-plugin lang-file plugin-slug lang_CODE\n");
    }

    // create plugin
    public function create() {
        $this->line();
        $this->line("Generating plugin");
        $this->line("-----------------");

        $this->template->ask_info();

        $hi = [];
        if ($this->template->fillHeader) {
            $this->askHeaderInfo();
            $hi = $this->headerInfo;
        }
        $this->line("\nReview your values and continue if everything is ok\n");
        $this->template->display_info($hi); 

        if (!$this->askYesNo("Install now? (Y/N)")) {
            $this->line("Cancelled by user.");
            return;
        }

        $this->set_template_info();
        
        if (!$this->copyFiles()) {
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
            $this->line("    php wp-vue-plugin install {$this->template->pluginSlug}");
        }

        $this->line();
        $this->line("The process is concluded, now we must start the development server,");
        $this->line("but before that we will try to activate the plugin '{$this->template->pluginName}' in WP.");

        if ($this->wpCli()) {
            $this->line();
            $this->line("Checking installed plugins...");
            $plugins = $this->getPlugins();
            if (!isset($plugins[$this->template->pluginSlug])) {
                $this->line();
                die("The plugin was not installed for some reason. Please clean all created files and run it again.");
            } else {
                $this->line();
                $this->line("✔ The plugin '{$this->template->pluginSlug}' was installed correctly");
            }
            if ($plugins[$this->template->pluginSlug]) {
                $this->line();
                $this->line("✔ The plugin '{$this->template->pluginSlug}' was already activated");
            } else {
                if ($this->askYesNo("Do you want to activate the plugin now? Y/N")) {
                    shell_exec("wp plugin activate {$this->template->pluginSlug}");
                    $plugins = $this->getPlugins();
                    if ($plugins[$this->template->pluginSlug]) {
                        $this->line();
                        $this->line("✔ The plugin '{$this->template->pluginSlug}' was activated");
                    }
                }
            }
            if ($plugins[$this->template->pluginSlug] && $this->template->post_type) {
                $this->line();
                $this->line("We can create a test post for you.");
                $this->line("To create it, type the post title and press Enter. Or just type Enter to ignore it.");
                $ptitle = readline();
                if (!empty($ptitle)) {
                    $call = "wp post create --post_type={$this->template->postTypeSlug} --post_title='{$ptitle}' --post_status=draft";
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
                    $this->line("The test post was not created. Go to WP admin and create a post of type '{$this->template->postTypeName}'.");
                }
                $this->line("Edit some {$this->template->postTypeName}, find the box '{$this->template->postTypeName}' in sidebar and click the button 'Toggle Vue app' to see the admin app.");
                $this->line("The shortcode is auto inserted in all posts of type {$this->template->postTypeName}, so just visit some post to see the frontend app.");
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
            $this->startServer($this->template->pluginSlug);
        } 
        else {
            $this->line();
            $this->line("To start the development server manually, just type:");
            $this->line();
            $this->line("    php wp-vue-plugin serve {$this->template->pluginSlug}");
            $this->askOpenCode();
        }
    }

    // copy base files to new plugin location
    public function copyFiles() {
        if (file_exists($this->template->pluginDirectory)) {
            $this->line();
            $this->line("The directory {$this->template->pluginDirectory} already exists. Remove it before you can create a new one.");
            return false;
        }
        mkdir($this->template->pluginDirectory);
        foreach ($this->base_directories as $dir) {
            mkdir("{$this->template->pluginDirectory}/{$dir}");
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
                shell_exec("cp -r {$dir}{$path} {$this->template->pluginDirectory}/{$pathDest}");
                continue;
            }
            $original = $dir . $path . $fileName;
            $newFileName = preg_replace_callback("/%([a-z]+)%/i", function($matches) {
                return $this->{$matches[1]};
            }, $newName);
            $destiny = "{$this->template->pluginDirectory}/{$path}{$newFileName}";
            shell_exec("cp {$original} {$destiny}");
        }
        $info = json_encode(get_object_vars($this->template), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents("{$this->template->pluginDirectory}/wp-vue-info.json", $info);
        
        $this->line();
        $this->line("✔ Files copied");
        return true;
    }

    // get some value from template object
    public function getTemplateVar($name) {
        if (property_exists($this->template, $name)) {
            return $this->template->{$name};
        }
        if (method_exists($this->template, $name)) {
            $ret = $this->template->{$name}();
            if (gettype($ret) == 'string') {
                return $ret;
            }
        }
        return null;
    }

    // replace strins in plugin files
    public function replaceStrings() {
        $files = array_merge(["{$this->template->pluginDirectory}/{$this->template->pluginSlug}.php"], $this->replacement_files);

        foreach ($files as $file) {
            $code = file_get_contents($file);
            foreach ($this->replacement_terms as $str => $var) {
                $val = $this->getTemplateVar($var);
                if ($val) {
                    $code = preg_replace("|" . preg_quote($str, "|") . "|", $val, $code);
                }
            }
            file_put_contents($file, $code);
        }

        $code = file_get_contents($files[0]);
        $code = preg_replace("/^<\?php/", "<?php\n" . $this->pluginHeader(), $code);
        file_put_contents($files[0], $code);
        
        $this->line();
        $this->line("✔ Replacements done");
    }

    // run 'npm install' in the creation process
    public function installPackages() {
        $this->line();
        $this->line("Navigate to {$this->template->pluginDirectory}/{$this->template->appDir} and install packages.");
        $this->line("This process can take a while...");
        $this->line();
        chdir("{$this->template->pluginDirectory}/{$this->template->appDir}");
        shell_exec("npm install");
        $this->line("✔ Installation complete");
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
            if (empty($this->template->i18nDir) || !file_exists("{$pluginDir}/{$this->template->i18nDir}")) {
                $this->line();
                $this->line("The plugin {$slug} does not seem to use I18n JSON translations.");
                return;
            }
            $path = "{$pluginDir}/{$this->template->i18nDir}/{$lang}.json";
            $files = $this->listFiles("{$pluginDir}/{$this->template->appDir}/src/components");
            $viewFiles = $this->listFiles("{$pluginDir}/{$this->template->appDir}/src/views");
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

    // delete entire plugin
    public function remove($slug) {
        if ($this->wpCli()) {
            $dir = trim(shell_exec("wp plugin path"));
            if (file_exists("{$dir}/{$slug}")) {
                shell_exec("rm -rf {$dir}/{$slug}");
            }
        }
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

    // adds the plugin header info
    public function pluginHeader() {
        $this->replaceHeaderItems();
        $items = ["Plugin name: {$this->template->pluginName}"];
        foreach ($this->headerInfo as $vname => $vval) {
            $val = $vval['value'] ?? $vval['default'] ?? '';
            $items[] = "{$vname}: {$val}";
        }
        return "/**\n * " . join("\n * ", $items) . "\n */";
    }

    // Replace %items% by template variables in header values
    public function replaceHeaderItems() {
        $this->headerInfo = array_map(function($e) {
            if (isset($e['default']) && preg_match("/%([a-z]+)%/i", $e['default'], $m) && property_exists($this->template, $m[1])) {
                $e['default'] = $this->template->{$m[1]};
            }
            if (isset($e['value']) && preg_match("/%([a-z]+)%/i", $e['value'], $m) && property_exists($this->template, $m[1])) {
                $e['value'] = $this->template->{$m[1]};
            }
            return $e;
        }, $this->headerInfo);
    }

    // Ask user for the header values
    public function askHeaderInfo() {
        $this->replaceHeaderItems();
        foreach ($this->headerInfo as $vname => $vval) {
            if (!isset($vval['question'])) {
                continue;
            }
            $def = $vval['default'] ?? '';
            $this->headerInfo[$vname]['value'] = $def;
            $this->line($vval['question'] . ($def ? " ({$def})" : ""));
            $answer = readline();
            if (empty($answer)) {
                if ($def) {
                    $answer = $def;
                } elseif (!empty($vval['required'])) {
                    while (empty($answer)) {
                        $this->line($vval['question']);
                        $answer = readline();
                    }
                }
            }
            $this->headerInfo[$vname]['value'] = $answer;
        }
    }
}