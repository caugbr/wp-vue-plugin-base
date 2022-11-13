# *WP-Vue Plugin base* templates
Este sistema foi pensado inicialmente como um boilerplate simples, um código inicial para se construir um plugin WP. Mas invés de simplesmente mostrar o que deveria ser substituído, resolvi dar um jeito de fazer as substituições automaticamente. Assim surgiu a ideia dessa ferramenta de linha de comando.

Por padrão vamos pedir ao usuário para definir as seguintes variáveis: (* obrigatório)
*  ```pluginName*``` - Nome legível do plugin
*  ```pluginSlug*``` - Nome usado em URLs
*  ```prefix*``` - Usado como prefixo em algumas funções e como textdomain pras traduções do WP
*  ```shortcodeName``` - Nome do shortcode
  
Se o plugin crria um post type
*  ```postTypeName``` - Nome do post type
*  ```postTypeNamePlural``` - Nome do tipo de post (plural)
*  ```postTypeSlug``` - Nome usado em URLs

Além dessas, cada template pode criar suas próprias variáveis.
Alguns outros valores internos serão combinações e adaptações dos valores definidos e não serão perguntados ao usuário.
  
## Criando uma template
Uma template precisa ter o arquivo ```/plugin-template.php``` contendo a ```class PluginTemplate```, que extende a ```abstract class TemplateBase```. Essa classe deve fornecer informações para a instalação e definir algumas variáveis. A classe ```TemplateBase``` é incluído automaticamente.

### Passo 1: criar o plugin
Cada modelo de template é um plugin completo e funcional, apenas contendo alguns termos que serão substituídos pelos valores do usuário no processo (esses termos precisam ser únicos). Quando seu plugin estiver pronto e funcionando, salve ele em ```/templates/[template-id]/plugin```.

### Passo 2: a classe ```PluginTemplate```
Agora precisamos criar a classe ```PluginTemplate```

### Variáveis
| Variável | Descrição | Valor padrão | Valor do usuário? | Obrigatório? |
| -- | -- | -- | :--: | :--: |
| pluginName | Nome do plugin | $argv[2] | Sim | Sim |
| pluginSlug | Nome para URLs | $utils->toSlug(pluginName) | Sim | Sim |
| prefix | Prefixo - uso geral | $utils->toPrefix(pluginName) | Não | Sim |
| shortcodeName | Nome para o shortcode | [prefix]_shortcode | Sim | Sim |
| postTypeName | Nome singular para o tipo de post | $argv[3] | Sim | Sim |
| postTypePlural | Nome plural para o tipo de post | [postTypeName]s | Sim | Sim |
| postTypeSlug | Nome para URLs | $utils->toSlug(postTypeName) | Sim | Sim |
| pluginDirectory | Caminho completo para o plugin | (...)/plugins/[pluginSlug] | Não | Sim |
| varName | Variável que contém a instâncîa do plugin | $utils->toSlug(postTypeName, '_') | Não | Sim |
| className | Nome para a classe | $utils->toClassName(postTypeName) | Não | Sim |
| args | Os argumentos digitados ao chamar o sistema. | - | - | - |
| flags | As flags enviadas (valores nomeados assim: -\-flag=valor) | - | - | - |
| templateVars | Contém todas as variáveis que serão definidas pelo usuário | - | - | - |
| fillHeader | Se TRUE, os valores no cabeçalho do plugin também serão definidos na instalação | TRUE | - | Sim |
| postType | Se TRUE, os dados para o post type serão perguntados | FALSE | - | Sim |
| appDir | O caminho para o diretório da Vue app, relativo á pasta do plugin | 'vue-app' | - | Sim |
| i18nDir | Se o plugin usa traduções i18n, o caminho para o diretório dos arquivos JSON | 'vue-app/src/i18n' | - | Sim |
| wpLangDir | Caminho completo para arquivos de tradução do WP  | 'langs' | - | Sim |
| devPort | Porta para executar o servidor de desenvolvimento  | 8080 | - | Não |

### Required methods
| Nome | Descrição |
| -- | -- |
| base_directories | Deve retornar um array com os diretórios que precisam ser criados |
| base_files | Deve retornar um array com os arquivos / diretórios que precisam ser copiados |
| replacement_files | Deve retornar um array com os arquivos que terão seu conteúdo substituído |
| replacement_terms | Deve retornar um array com os termos a serem substituídos e suas respectivas substituições |