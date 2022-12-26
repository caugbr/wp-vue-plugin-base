# *WP-Vue Plugin base* templates
Este sistema foi pensado inicialmente como um boilerplate simples, um código inicial para se construir um plugin WP. Mas invés de simplesmente mostrar o que deveria ser substituído no código, resolvi dar um jeito de fazer as substituições automaticamente. Assim surgiu a ideia dessa ferramenta de linha de comando e, por conta dessa arquitetura, podemos usar vários templates diferentes.

Por padrão vamos pedir ao usuário para definir as seguintes variáveis: (* obrigatório)
*  `pluginName*` - Nome legível do plugin
*  `pluginSlug*` - Nome usado em URLs
*  `prefix*` - Usado como prefixo em algumas funções e como textdomain pras traduções do WP
*  `shortcodeName` - Nome do shortcode
  
Se o plugin cria um post type
*  `postTypeName` - Nome do post type
*  `postTypeNamePlural` - Nome do tipo de post (plural)
*  `postTypeSlug` - Nome usado em URLs

Além dessas, cada template pode criar suas próprias variáveis.
Alguns outros valores internos serão combinações e adaptações dos valores definidos e não serão perguntados ao usuário.
  
## Criando uma nova template
Uma template deve conter o plugin base e um arquivo com o mesmo nome do diretório (o id da template seguido de '.php') - algo como `/templates/[template-id]/[template-id].php`, que deve fornecer informações para a instalação e definir algumas variáveis.

### Passo 1: criar o plugin
Cada template é um plugin completo e funcional, apenas contendo alguns termos que serão substituídos pelos valores do usuário no processo (esses termos precisam ser únicos).

### Passo 2: a classe do plugin
Agora precisamos criar a classe no arquivo `[template-id].php`, contendo a `class [TemplateId]` (my-plugin.php <=> MyPlugin), que extende a `abstract class TemplateBase`. Essa classe vai definir os detalhes do processo, desde a coleta de dados do usuário até o novo plugin estar pronto, personalizado e ativo. A classe `TemplateBase` já estará presente e não precisa ser incluída.

Formas que a template pode influenciar o processo de geração do plugin

#### Coleta de dados
Há algumas variáveis padrão que o sistema necessita (tabela abaixo), mas a template pode acrescentar outras personalizadas que serão definidas pelo usuário e poderão ser usadas para as substituições. Use para isso a propriedade `templateVars`, adicionando suas variáveis.

Para adicionar uma variável customizada:

    $this->templateVars['variableName'] = [ // Nome da variável
        // Pergunta apresentada ao usuário
        "question" => "Question to user", 
        // Valor sugerido ao usuário (opcional)
        "default" => "Default value", 
        // Um método pra receber o valor digitado (opcional)
        "handler" => "methodName", 
        // Se TRUE, a questão se repetirá até ser respondida (opcional)
        "required" => true 
    ];

#### Clonagem dos arquivos
Os métodos `base_directories` e `base_files` são obrigatórios e os arrays retornados por eles definirão, respectivamente, que diretórios precisam ser criados e que arquivos (ou diretórios inteiros) devem ser copiados para o novo local. Os caminhos são relativos ao diretório do plugin.

**`base_directories()`**

    public  function  base_directories() {
        return ["langs"]; // o diretório '/langs' será criado
    }

**`base_files()`**
Aqui podemos renomear os arquivos. Os índices definem a origem dos arquivos (caminho relativo ao root do plugin), enquanto os valores definem o novo nome dos arquivos (apenas o nome, sem o caminho). Se o arquivo estiver em um diretório, ao ser copiado será colocado nessa mesma pasta - que deve ser previamente criada.
Para copiar um diretório com todo o seu conteúdo, use '/*'.

    public  function  base_files() {
        return [
            'vue-plugin-base.php' => "{$this->pluginSlug}.php",
            'langs/prefix-pt_BR.mo' => "{$this->prefix}-pt_BR.mo",
            'langs/prefix-pt_BR.po' => "{$this->prefix}-pt_BR.po",
            'vue-app/*' => '*'
        ];
    }

#### Substituição dos termos
Temos os métodos `replacement_files` e `replacement_terms` para definir o que será substituído e que arquivos passarão pelo processo de substituição.

**`replacement_files()`**

    public  function  replacement_files() {
        return [
            "{$this->pluginDirectory}/langs/{$this->prefix}-pt_BR.po",
            "{$this->pluginDirectory}/vue-app/src/components/Wp/api.js",
            "{$this->pluginDirectory}/vue-app/package.json"
        ];
    }

**`replacement_terms()`**
Os índices definem os termos que serão buscados e substituídos e os valores do array, os nomes das propriedades (ou métodos) que devem substituir cada respectivo termo.

    public  function  replacement_terms() {
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

### Variáveis
| Variável | Descrição | Valor padrão | Valor do usuário? | Obrigatório? |
| -- | -- | -- | :--: | :--: |
| pluginName | Nome do plugin | $argv[2] | Sim | Sim |
| pluginSlug | Nome usado em URLs | $utils->toSlug(pluginName) | Sim | Sim |
| prefix | Prefixo para uso geral | $utils->toPrefix(pluginName) | Não | Sim |
| shortcodeName | Nome para o shortcode | [prefix]_shortcode | Sim | Sim |
| postTypeName | Nome singular para o tipo de post | $argv[3] | Sim | Sim |
| postTypePlural | Nome plural para o tipo de post | [postTypeName]s | Sim | Sim |
| postTypeSlug | Nome para URLs | $utils->toSlug(postTypeName) | Sim | Sim |
| pluginDirectory | Caminho completo para o plugin | (...)/plugins/[pluginSlug] | Não | Sim |
| varName | Variável que vai receber instância do plugin | $utils->toSlug(postTypeName, '_') | Não | Sim |
| className | Nome para a classe | $utils->toClassName(postTypeName) | Não | Sim |
| args | Os argumentos digitados ao chamar o sistema. | - | - | - |
| flags | As flags enviadas (valores nomeados assim: -\-flag=valor) | - | - | - |
| templateVars | Contém todas as variáveis que serão definidas pelo usuário | - | - | - |
| fillHeader | Se TRUE, os valores no cabeçalho do plugin também serão definidos na instalação | TRUE | - | Sim |
| postType | Se TRUE, os dados para o post type serão perguntados | FALSE | - | Sim |
| appDir | O caminho para o diretório da Vue app, relativo á pasta do plugin | 'vue-app' | - | Sim |
| i18nDir | Se o plugin usa traduções i18n, o caminho para o diretório dos arquivos JSON | 'vue-app/src/i18n' | - | Sim |
| wpLangDir | Caminho completo para arquivos de tradução do WP  | 'langs' | - | Sim |
| devHost | Host URL | 'http://127.0.0.1' | - | Sim |
| devPort | Porta para executar o servidor de desenvolvimento  | 8080 | - | Não |

### Métodos obrigatórios
| Nome | Descrição |
| -- | -- |
| base_directories | Deve retornar um array com os diretórios que precisam ser criados |
| base_files | Deve retornar um array com os arquivos / diretórios que precisam ser copiados |
| replacement_files | Deve retornar um array com os arquivos que terão seu conteúdo substituído |
| replacement_terms | Deve retornar um array com os termos a serem substituídos e suas respectivas substituições |