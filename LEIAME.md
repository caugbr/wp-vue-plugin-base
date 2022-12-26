# WP-Vue plugin base
Este script não é um plugin Wordpress. Embora precise ser colocado no diretório de plugins, trata-se de uma ferramenta PHP para linha de comando que gera um plugin já integrado com o Vue. É um boilerplate dinâmico que é criado já com os nomes que o usuário definir e que é entregue pronto, instalado e funcionando.

## WP-CLI
O WP-Vue plugin base funciona melhor junto com o [WP-CLI](https://wp-cli.org/). Se você não tiver ele instalado, certas coisas não serão possíveis. Mesmo assim você pode utilizar o comando `create` para criar o seu plugin (com algumas restrições), mas todos os outros comandos dependem do WP-CLI e não irão funcionar.

## Templates
É o código base para o plugin a ser criado, que chamamos de 'template', que define como a criação vai acontecer, que valores serão substituídos em que arquivos, etc. E o sistema é capaz de trabalhar com diferentes templates. 

### A template 'post-type'
Essa é a template padrão do sistema e seu código inclui a criação de um post type e duas views já funcionais. O componente Backend.vue estará disponível na página de edição do tipo de post criado, fornecendo uma forma de sobrepor o editor e criar uma edição personalizada para esse tipo. Já o componente Frontend.vue foi pensado para exibir esse tipo no site e pode ser incluído em qualquer publicação via shortcode.

### A template 'shortcode'
Essa é uma versão similar, mas sem a parte da administração. Com essa template você terá apenas um shortcode pra exibir sua Vue app no site, provavelmente usando dados de algum post via REST API.

## Vue app
* Vuex
* Sass
* Alguns componentes customizados
  * I18n - *uma forma simples de traduzir strings armazenadas em arquivos JSON, com base na linguagem do WP. Usado como um plugin do Vue.*
  * Wait overlay - *loading overlay.*
  * User message - *barra de mensagens ao usuário.*
  * Wp/AdminLayout - *uma layer com layout similar ao Gutemberg editor.*
  * Wp/MetaBox - *metabox para usar no sidebar do componente AdminLayout.*

## Como usar

Navegue até `.../wp-content/plugins/wp-vue-plugin-base` e, usando o prompt, execute o comando desejado. Todos os comandos precisam ser executados nesse diretório.

---------
Os comandos disponíveis são:

**TEMPLATES**
Use o comando 'templates' para ver a lista de templates disponíveis.

    php wp-vue-plugin templates

---------
**CREATE**
O comando 'create' cria um novo plugin Wordpress.

    php wp-vue-plugin create

Para escolher um outro template, use a flag 'template'.

    php wp-vue-plugin create --template=template-id

O sistema vai pedir para o usuário definir alguns valores. Se houver um valor entre parênteses, trata-se de uma sugestão gerada a partir de valores já informados. Tecle [Enter] para aceitar a sugestão.

Uma vez iniciado, o processo irá:

* Copiar todos os arquivos para o local do novo plugin
* Substituir as strings pelos valores definidos pelo usuário
* Instalar os packages via NPM
* Verificar se o plugin foi instalado no WP (depende do WP-CLI)
* Ativar o plugin na administração (opcional - depende do WP-CLI)
* Publicar um post de teste (opcional - depende do WP-CLI)
* Abrir o arquivo do plugin no Visual code (opcional)
* Iniciar o servidor de desenvolvimento  (opcional)

Nesse ponto o usuário tem tudo pronto para começar a trabalhar.

--------
**INSTALL**
Instala ou reinstala os packages do plugin via NPM

    php wp-vue-plugin install plugin-slug

Para fazer isso diretamente, navegue até o diretório vue-app do plugin e execute `npm install`.

--------
**SERVE**
O comando 'serve' inicia o servidor de desenvolvimento para o plugin desejado

    php wp-vue-plugin serve plugin-slug

Para fazer isso diretamente, navegue até o diretório vue-app do plugin e execute `npm run serve`.

--------
**BUILD**
O comando 'build' gera o pacote final de produção para publicar o seu site.

    php wp-vue-plugin build plugin-slug

Para fazer isso diretamente, navegue até o diretório vue-app do plugin e execute `npm run build`.

--------
**LANG-FILE**
Esse comando gera ou atualiza arquivos de tradução em JSON para o I18n, buscando as strings a serem traduzidas no código do plugin. Essas são as strings que o usuário enviou para as funções `t()` e `tl()`.

    php wp-vue-plugin lang-file plugin-slug lang_CODE

Use esse comando para iniciar uma nova tradução ou atualizar um arquivo com novas strings adicionadas ao código.

--------
**HELP**
Mostra textos de ajuda para os comandos disponíveis ou apenas para um, se o parâmetro `command` for enviado.

      php wp-vue-plugin help [command]