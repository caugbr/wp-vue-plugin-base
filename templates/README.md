# *WP-Vue Plugin* templates
This system was initially made as a boilerplate, a starter code using a plugin model that was ready, but instead of just indicating which terms should be replaced in the code, I decided to make the replacements automatically through a PHP for command line script.
Soon I realized the possibility of having more than one plugin model and I turned the WP-Vue plugin into a more generic installer, capable of installing different plugin templates, delegating to templates the definition of what should be copied and what terms should be replaced in which files. In addition, the system can install npm packages, start the development server, among ohter things. The user can also choose to activate the plugin in WP and create a test post, if the template includes a post type.
Now we have a plugins repository and the system can install any of them. At the moment there are only two, but the possibility is open for us to add more over time.

By default we will ask the user to enter data for the following variables: (* required)
*  ```pluginName*``` - Legible plugin name
*  ```pluginSlug*``` - Name used in URLs
*  ```prefix*``` - Used as prefix in some functions and as textdomain to WP translations
*  ```shortcodeName``` - Shortcode name
  
If the plugin creates a post type
*  ```postTypeName``` - Post type name
*  ```postTypeNamePlural``` - Post type name plural
*  ```postTypeSlug``` - Name used in URLs
  
In addition, each template can create its own variables.
There are some other values used that are combinations of the variables above and fixed strings that are not asked to the user.
  
## Creating a new template
A template must have a file called ```/plugin-template.php``` containing the ```class PluginTemplate```, that extendends the ```abstract class TemplateBase```. This class must provide the installation information and define some variables. The ```TemplateBase``` will be already included, dont worry about it.

### First step: create the plugin
Each model is a fullt functional WP plugin, just containing some terms that will be replaced in process.
After your plugin is ready and unctional, save it in ```/templates/[template-id]/plugin```, using unique terms in your code to each detail that should be replaced by a variable.

### Second step: the ```PluginTemplate``` class
Now you have to create a class named ```PluginTemplate```

### Variáveis
| Variable | Description | Default value | Asked to user? | Required? |
| -- | -- | -- | :--: | :--: |
| pluginName | Plugin name | $argv[2] | Yes | Yes |
| pluginSlug | Name for URLs | $utils->toSlug(pluginName) | Yes | Yes |
| prefix | Prefix for some functions | $utils->toPrefix(pluginName) | No | Yes |
| shortcodeName | Name for the shortcode | [prefix]_shortcode | Yes | Yes |
| postTypeName | Post type singular name | $argv[3] | Yes | Yes |
| postTypePlural | Post type plural name |[postTypeName]s | Yes | Yes |
| postTypeSlug | Name for URLs | $utils->toSlug(postTypeName) | Yes | Yes |
| pluginDirectory | The full path to the new plugin | (...)/plugins/[pluginSlug] | No | Yes |
| varName | PHP variable that holds the plugin instance | $utils->toSlug(postTypeName, '_') | No | Yes |
| className | Name for the plugin class | $utils->toClassName(postTypeName) | No | Yes |
| args | The arguments typed on calling the system. | -|- | - |
| flags | The flags sent (named values like: -\-flag=value) | -|- | - |
| template_vars | Contains all variablesthat will be asked to the user | -|- | - |
| fillHeader | If TRUE the header fields will be asked | TRUE | -|Yes |
| postType | If TRUE the post type fields will be asked | FALSE | -|Yes |
| appDir | The path to Vue app dir, relative to plugin folder | 'vue-app' | -|Yes |
| i18nDir | If your plugin uses i18n JSON translations, the path to translation files | 'vue-app/src/i18n' | -|Yes |
| wpLangDir | Path to WP translation files | 'langs' | -|Yes |

### Required methods
| Name | Description |
| -- | -- |
| base_directories | Should return the directories that must be created |
| base_files | Should return the files / directories that must be copied |
| replacement_files | Should return the files that will have it's content replaced |
| replacement_terms | Should return the terms to replace and it's replacements |