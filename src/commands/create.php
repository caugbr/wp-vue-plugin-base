<?php

require_once dirname(__FILE__) . "/../utils.php";
require_once dirname(__FILE__) . "/../wp-cli.php";
require_once dirname(__FILE__) . "/../templates.php";
require_once dirname(__FILE__) . "/../npm.php";

class Create {
    
    use WpVuePluginBaseUtils;
    use WpVuePluginBaseWpCli;
    use WpVuePluginBaseTemplates;
    use WpVuePluginBaseNpm;

    public $pluginName = "";
    public $postTypeName = "";
    public $slug = "";
    public $expectedArgs = ["pluginName", "postTypeName"];
    public $description = "The command CREATE generates a new Wordpress plugin integrated with Vue 2";

    public $args;
    // public $flags;

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

    public function __construct($args, $instance) {
        // print_r($instance); 
        $this->args2props($args);
        $this->flags = $instance->flags;
        $this->localPath = $instance->localPath;
        $this->template = $instance->template;
        $this->instance = $instance;
        $this->doIt();
    }

    // create plugin
    public function doIt() {
        $this->line();
        $this->line("Generating a new plugin");
        $this->line("-----------------------");

        $this->instance->template->ask_info();

        $hi = [];
        if ($this->instance->template->fillHeader) {
            $this->askHeaderInfo();
            $hi = $this->headerInfo;
        }
        $this->line("\nReview your values and continue if everything is ok\n");
        $this->instance->template->display_info($hi); 

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
            // $this->installPackages(); // ???
        } else {
            $this->line();
            $this->line("Your app is not functional yet. You must install the project dependencies.");
            $this->line("To install packages manually, just type:");
            $this->line();
            $this->line("    php wp-vue-plugin install {$this->instance->template->pluginSlug}");
        }

        $this->line();
        $this->line("The process is concluded, now we must start the development server,");
        $this->line("but before that we will try to activate the plugin '{$this->instance->template->pluginName}' in WP.");

        if ($this->wpCli()) {
            $this->line();
            $this->line("Checking installed plugins...");
            $plugins = $this->getPlugins();
            // print "PLUGINS:\n";
            // print_r($plugins);
            if (!isset($plugins[$this->instance->template->pluginSlug])) {
                $this->line();
                die("The plugin was not installed for some reason. Please clean all created files and run it again.");
            } else {
                $this->line();
                $this->line("✔ The plugin '{$this->instance->template->pluginSlug}' was installed correctly");
            }
            if ($plugins[$this->instance->template->pluginSlug]) {
                $this->line();
                $this->line("✔ The plugin '{$this->instance->template->pluginSlug}' was already activated");
            } else {
                if ($this->askYesNo("Do you want to activate the plugin now? Y/N")) {
                    shell_exec("wp plugin activate {$this->instance->template->pluginSlug}");
                    $plugins = $this->getPlugins();
                    if ($plugins[$this->instance->template->pluginSlug]) {
                        $this->line();
                        $this->line("✔ The plugin '{$this->instance->template->pluginSlug}' was activated");
                    }
                }
            }
            if ($plugins[$this->instance->template->pluginSlug] && $this->instance->template->post_type) {
                $this->line();
                $this->line("We can create a test post for you.");
                $this->line("To create it, type the post title and press Enter. Or just type Enter to ignore it.");
                $ptitle = readline();
                if (!empty($ptitle)) {
                    $call = "wp post create --post_type={$this->instance->template->postTypeSlug} --post_title='{$ptitle}' --post_status=draft";
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
                    $this->line("The test post was not created. Go to WP admin and create a post of type '{$this->instance->template->postTypeName}'.");
                }
                $this->line("Edit some {$this->instance->template->postTypeName}, find the box '{$this->instance->template->postTypeName}' in sidebar and click the button 'Toggle Vue app' to see the admin app.");
                $this->line("The shortcode is auto inserted in all posts of type {$this->instance->template->postTypeName}, so just visit some post to see the frontend app.");
            }
        } else {
            $this->line();
            $this->line("We could not activate the plugin, you must activate your plugin manually.");
        }

        if ($this->askYesNo("Run the development server here? Y/N")) {
            $this->line();
            $this->instance->askOpenCode();
            $this->line();
            $this->line("The development server will be running here. Do not close this window. Ctrl + C to stop.");
            $this->startServer($this->instance->template->pluginSlug);
        } 
        else {
            $this->line();
            $this->line("To start the development server manually, just type:");
            $this->line();
            $this->line("    php wp-vue-plugin serve {$this->instance->template->pluginSlug}");
            $this->instance->askOpenCode();
        }
    }

    // copy base files to new plugin location
    public function copyFiles() {
        if (file_exists($this->instance->template->pluginDirectory)) {
            $this->line();
            $this->line("The directory {$this->instance->template->pluginDirectory} already exists. Remove it before you can create a new one.");
            return false;
        }
        mkdir($this->instance->template->pluginDirectory);
        foreach ($this->instance->template->base_directories() as $dir) {
            mkdir("{$this->instance->template->pluginDirectory}/{$dir}");
        }

        foreach ($this->instance->template->base_files() as $path => $newName) {
            $pathParts = explode("/", $path);
            $fileName = array_pop($pathParts);
            $dir = "{$this->instance->localPath}/templates/{$this->instance->templateName}/plugin/";
            $path = join("/", $pathParts);
            if ($path) {
                $path .= "/";
            }
            if($fileName === '*') {
                $pathDest = str_replace("/" . array_pop($pathParts), "", $path);
                shell_exec("cp -r {$dir}{$path} {$this->instance->template->pluginDirectory}/{$pathDest}");
                continue;
            }
            $original = $dir . $path . $fileName;
            $newFileName = preg_replace_callback("/%([a-z]+)%/i", function($matches) {
                return $this->instance->{$matches[1]};
            }, $newName);
            $destiny = "{$this->instance->template->pluginDirectory}/{$path}{$newFileName}";
            shell_exec("cp {$original} {$destiny}");
        }
        $info = json_encode(get_object_vars($this->instance->template), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents("{$this->instance->template->pluginDirectory}/wp-vue-info.json", $info);
        
        $this->line();
        $this->line("✔ Files copied");
        return true;
    }

    // replace strings in plugin files
    public function replaceStrings() {
        $files = array_merge([
            "{$this->instance->template->pluginDirectory}/{$this->instance->template->pluginSlug}.php"
        ], $this->instance->template->replacement_files());

        foreach ($files as $file) {
            $code = file_get_contents($file);
            foreach ($this->instance->template->replacement_terms() as $str => $var) {
                $val = $this->get_template_var($var);
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