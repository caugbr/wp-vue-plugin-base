# WP-Vue plugin base
This script is not a Wordpress plugin. Although it must be placed in the plugins directory, it is a command line tool that generates a WP plugin, a starter code to build your own plugin, already integrated with Vue 2.

## WP-CLI
WP-Vue plugin base works better with [WP-CLI](https://wp-cli.org/). If you don't have it installed in you machine, some steps will be skiped and some things will not be possible. Anyway you can use it to create you plugin, but all other commands depends on WP-CLI and will not work.

## Plugin templates
This version has two different plugins, but the most important, the possibility to add new templates with other designs and features.

### The 'post-type' template
The starter code comes with all Vue related things done and two views already linked and functional. The component Backend.vue is loaded in post edit page and the Frontend.vue will be loaded using a shortcode. This is the default template.

Things you'll have to start:

* a new post type
* a metabox on this post type edit page with a button to show the Vue app layer
* a shortcode to display the frontend app in your site
* a helper script to use the WP Rest API in your Vue app
* all needed stuff to integrate WP with Vue

### The 'shortcode' template
This is a similar version, but without the admin part. Here you'll have only a shortcode to display the Vue app in your site.

Things you'll have to start:

* a shortcode to display the frontend app in your site
* a helper script to use the WP Rest API in your Vue app
* all needed stuff to integrate WP with Vue

## The Vue app
* Vuex
* Sass
* Some custom components
  * I18n - *a simple way translate strings stored on JSON files, based in WP language. Used as a Vue plugin.*
  * Wait overlay - *a loading overlay.*
  * User message - *a top bar to show messages to the user.*
  * Wp/AdminLayout - *a layer with the same layout of Gutemberg editor.*
  * Wp/MetaBox - *creates a metabox to use in the sidebar of AdminLayout.*

## How to use it
Navigate to `.../wp-content/plugins/wp-vue-plugin-base` using the prompt and run the desired command.
All commands must run under this directory.

---------
Available commands are:

**TEMPLATES**
Use the command 'templates' to see the available plugin templates.

    php wp-vue-plugin templates

---------
**CREATE**
The command 'create' generates a new Wordpress plugin integrated with Vue 2.

    php wp-vue-plugin create
 
You can change the template using one of the slugs this command will show with the flag 'template'.

    php wp-vue-plugin create --template=template-id

It will ask you to define some names. If there is a value between parentheses, this the auto generated value. Type your own value to replace it or just press [Enter] to accept the suggestion.

Once started, the process will:

* Copy all files to the new plugin location
* Replace all key strings by your values
* Go to the new Vue app and install packages if you want (recommended)
* Verify if the plugin was installed (depends on WP-CLI)
* Activate your plugin if you want (depends on WP-CLI)
* Publish a test post if you want (depends on WP-CLI)
* Open the plugin file in Visual code if you want
* Start the development server

At this point, everything is done. Just start working.

--------
**INSTALL**
Install the project packages if you did not when creating or if you want to reinstall packages.

    php wp-vue-plugin install plugin-slug

You can do it manually. Just open prompt in your plugin directory, navigate to ./vue-app and run `npm install`.

--------
**SERVE**
The command 'serve' starts the development server to the given plugin.

    php wp-vue-plugin serve plugin-slug

You can start server here using the generated plugin slug. Other way is to open prompt in your plugin directory, navigate to ./vue-app and run `npm run serve`.

--------
**BUILD**
The command 'build' generates the production package to publish your site.

    php wp-vue-plugin build plugin-slug

You can build the production package from here using the generated plugin slug. Other way is to open prompt in your plugin directory, navigate to ./vue-app and run `npm run build`.

--------
**LANG-FILE**
The command 'lang-file' generates a new I18n translation file (JSON), with all translatable strings on it.
These are the strings sent to the translation functions `t()` and `tl()`. They will be extracted from your Vue files.
 
    php wp-vue-plugin lang-file plugin-slug lang_CODE

Use this command to start a new translation. It will read all occurrences of `t('Some string')` and `tl('Some string')` and put it all into the new file.