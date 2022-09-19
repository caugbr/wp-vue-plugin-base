# WP-Vue plugin base

This script is not a Wordpress plugin. Although it must be placed in the plugins directory, it is a command line tool that generates a WP plugin, a starter code to build your own plugin, already integrated with Vue 2.

The generated plugin has a specific design and will be useful if you need to create a post type and display/edit it using Vue apps.

In the administration part, there is a meta box when creating/editing a post (of the type we created) that opens a layer over the WP editor, with the same layout. This allows you to build your own editor for your content type.

On the site, this content will be displayed via shortcode, which is automatically included in the post when we create it.

## WP-CLI

This script works better with [WP-CLI](https://wp-cli.org/). If you don't have it installed in you machine, some steps will be skiped and some things will not be possible. Anyway you can use it to create you plugin, but all other commands depends on WP-CLI and will not work.

## The plugin

The starter code comes with all Vue related things done and two views already linked and functional. The component Backend.vue is loaded in post edit page and the Frontend.vue will be loaded using a shortcode.
Some things you'll have ready on start:

* a new post type
* a metabox on this post type edit page with a button to show the Vue app layer
* a shortcode to display the frontend app in your site
* a helper script to use the WP Rest API in your Vue app
* some functions needed to Vue integration

## The Vue app

* Vuex
* Sass
* Some custom components
  * I18n
    *a simple way translate strings stored on JSON files, based in WP language. Used as a Vue plugin.*
  * Wait overlay
    *a loading overlay.*
  * User message
    *a top bar to show messages to the user.*
  * Wp/AdminLayout
    *a layer with the same layout of Gutemberg editor.*
  * Wp/MetaBox
    *this component creates a metabox, to use with AdminLayout, in sidebar.*

## How to use it

Navigate to `.../wp-content/plugins/wp-vue-plugin-base` using the prompt and run the desired command.
All commands must run under this directory

Available commands:

**CREATE**

The command 'create' generates a new Wordpress plugin integrated with Vue 2.

    php wp-vue-plugin create

It will ask you to define some names. If there is a value between parentheses, this the auto generated value. Type your own value to replace it or just press [Enter] to accept the suggestion. If everything is ok, type 'Yes' to create.

Once started, the process will:

* Copy all files from the base to the new plugin
* Replace all key strings by your values
* Go to the new Vue app and install packages
* Verify if the plugin was installed (depends on WP-CLI)
* Activate your plugin if you want (depends on WP-CLI)
* Publish a test post if you want (depends on WP-CLI)
* Open the plugin file in Visual code if you want
* Start the development server

At this point, everything is done. Just start working.

--------
**SERVE**
The command 'serve' starts the development server to the given plugin.

    php wp-vue-plugin serve plugin-slug

You can start server here using the generated plugin slug. Other way is to open prompt in your plugin directory, navigate to ./vue-app and run `npm run serve`.

------
**BUILD**
The command 'build' generates the production package to publish your site.

    php wp-vue-plugin build plugin-slug

You can build the production package from here using the generated plugin slug. Other way is to open prompt in your plugin directory, navigate to ./vue-app and run `npm run build`.

----
**LANG-FILE**

The command 'lang-file' generates a new translation file (JSON), with all translatable strings on it.
These are the strings sent to the translation functions t() and tl(). They will be extracted from your Vue files.

    php wp-vue-plugin lang-file plugin-slug lang-code

Use this command to start a new translation. It will read all occurrences of `t('Some string')` and `tl('Some string')` and put it all into the new file.