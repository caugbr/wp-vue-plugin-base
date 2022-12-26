# *WP-Vue Plugin base* templates
This system was initially thought of as a simple boilerplate, a starter code to build a WP plugin. But instead of simply showing what should be replaced in the code, I decided to find a way to do the replacements automatically. That's how the idea of this command line tool came up and, because of this architecture, we can use several different plugin templates.

By default we will ask the user to define the following variables: (* required)
* `pluginName*` - Human-readable name of the plugin
* `pluginSlug*` - Name used in URLs
* `prefix*` - Used as a prefix in some functions and as a textdomain for WP translations
* `shortcodeName` - Name to the WP shortcode
  
If the plugin creates a post type
* `postTypeName` - Post type name
* `postTypeNamePlural` - Post type name (plural)
* `postTypeSlug` - Name used in URLs

In addition, each template can create its own variables.
Some other internal values will be created from combinations and adaptations of the defined values and will not be asked to the user.
  
## Creating a new template
A template should contain the base plugin and a file with the same name as the directory (the `template id` followed by '.php') - something like `/templates/[template-id]/[template-id].php`, which should provide information for the installation and set some variables.

### Step 1: Create the plugin
Each plugin template is a complete and functional plugin, only containing a few terms that will be replaced by the user values in the process (these terms need to be unique). When your plugin is up and running, save it on `/templates/[template-id]/plugin`.

### Step 2: The `PluginTemplate` class
Now we need to create the `class [TemplateId]` in the `[template-id].php` file (my-plugin.php <=> MyPlugin), which extends the `abstract class TemplateBase`. This class will define the details of the process, from collecting user data until the new plugin is ready, customized and active. The `TemplateBase` class will already be present and does not need to be included.

Ways the template can influence the plugin generation process

#### Data collect
There are some default variables that the system needs (table below), but the template can add custom ones that will be defined by the user and can be used for substitutions. Use the `templateVars` property for this, adding your variables.

To add a custom variable:

     $this->templateVars['variableName'] = [ // Variable name
         // Question presented to the user
         "question" => "Question to user",
         // User suggested value (optional)
         "default" => "Default value",
         // A method to receive the typed value (optional)
         "handler" => "methodName",
         // If TRUE, the question will repeat until answered (optional)
         "required" => true

#### Cloning the files
The `base_directories` and `base_files` methods are mandatory and the arrays returned by them will define, respectively, which directories need to be created and which files (or entire directories) must be copied to the new location. Paths are relative to the plugin directory.

**`base_directories()`**

    public function base_directories() {
        return ["langs"]; // directory '/langs' will be created
    }

**`base_files()`**
Here we can rename the files. The indexes define the source of the files (path relative to the root of the plugin), while the values define the new name of the files (just the name, without the path). If the file is in a directory, when copied it will be placed in that same folder - which must be previously created.
To copy a directory with all its contents, use '/*'.

    public function base_files() {
        return [
            'vue-plugin-base.php' => "{$this->pluginSlug}.php",
            'langs/prefix-pt_BR.mo' => "{$this->prefix}-pt_BR.mo",
            'langs/prefix-pt_BR.po' => "{$this->prefix}-pt_BR.po",
            'vue-app/*' => '*'
        ];
    }

#### Substitution of terms
We have the `replacement_files` and `replacement_terms` methods to define what will be replaced and which files will go through the replacement process.

**`replacement_files()`**

    public function replacement_files() {
        return [
            "{$this->pluginDirectory}/langs/{$this->prefix}-pt_BR.po",
            "{$this->pluginDirectory}/vue-app/src/components/Wp/api.js",
            "{$this->pluginDirectory}/vue-app/package.json"
        ];
    }

**`replacement_terms()`**
The indices define the terms that will be searched and replaced and the values, the names of the properties (or methods) that must replace each respective term.

    public function replacement_terms() {
        return [
            "VuePluginBase" => "className",
            "vue-plugin-slug" => "pluginSlug",
            "Plugin name" => "pluginName",
            "VPB posts" => "postTypeNamePlural",
            "VPB post" => "postTypeName",
            "prefix_post" => "postTypeId",
            "vue_plugin_base" => "varName",
            "prefix_shortcode" => "shortcodeName",
            "prefix" => "prefix",
            "WP-Vue plugin base post type" => "postTypeDescription",
            "http://localhost:8080" => "devUrl"
        ];
    }

### Variables
| Variable | Description | Default value | User value? | Mandatory? |
| -- | -- | -- | :--: | :--: |
| pluginName | Plugin name | $argv[2] | Yes | Yes |
| pluginSlug | Name used in URLs | $utils->toSlug(pluginName) | Yes | Yes |
| prefix | Prefix for general use | $utils->toPrefix(pluginName) | No | Yes |
| shortcodeName | Name for the shortcode | [prefix]_shortcode | Yes | Yes |
| postTypeName | Singular name for the post type | $argv[3] | Yes | Yes |
| postTypePlural | Plural name for post type | [postTypeName]s | Yes | Yes |
| postTypeSlug | Name for URLs | $utils->toSlug(postTypeName) | Yes | Yes |
| pluginDirectory | Full path to plugin | (...)/plugins/[pluginSlug] | No | Yes |
| varName | Variable that will receive the plugin instance | $utils->toSlug(postTypeName, '_') | No | Yes |
| className | Name for the class | $utils->toClassName(postTypeName) | No | Yes |
| args | The arguments entered when calling the system. | | | |
| flags | The flags sent (values named like this: -\-flag=value) | | | |
| templateVars | Contains all variables that will be defined by the user | | | |
| fillHeader | If TRUE, the values in the plugin header will also be set at installation | TRUE | | Yes |
| postType | If TRUE, data for the post type will be asked | FALSE | | Yes |
| appDir | The path to the Vue app directory, relative to the plugin folder | 'vue-app' | | Yes |
| i18nDir | If the plugin uses i18n translations, the path to the JSON files directory | 'vue-app/src/i18n' | | Yes |
| wpLangDir | Full path to WP translation files | 'langs' | | Yes |
| devHost | Host URL | 'http://127.0.0.1' | | Yes |
| devPort | Port to run development server | 8080 | | No |

### Required methods
| Name | Description |
| -- | -- |
| base_directories | It should return an array with the directories that need to be created |
| base_files | It should return an array with the files / directories that need to be copied |
| replacement_files | It must return an array with the files that will have their content replaced |
| replacement_terms | It should return an array with the terms to be replaced and their respective substitutions |