# OsChatWhatsApp
Sistema de chatbot integrado a API Oficial da Meta

Requisitos:

PHP >= 7.4;
Conta empresarial da Meta, chip verificado e configurado, mais informações em https://developers.facebook.com/docs/whatsapp/cloud-api/overview.

Chatbot (Foram feitas diversas modificações no sistema do chatbot ao longo do tempo). Entre elas estão:

(ABRIL, MAIO e JUNHO):
Adicionado o campo do token do cron do sistema na tela de opções do sistema.
Adicionada o campo do ID da conta do WhatsApp Business na tela de opções do sistema.
Adicionado o campo de seleção para selecionar o número que será utilizado pelo bot para o recebimento e envio de mensagens.
Corrigido erro que desvinculava as opções a um menu quando novas opções são adicionadas.
Página de atendentes finalizada.
Agora é possível enviar vários menus e submenus. Aumentando a interatividade do Bot.
Adicionados novos eventos "Ao atendente iniciar a conversa", "Ao atendente finalizar a conversa", "Ao cliente finalizar a conversa", "Ao tempo de conversa encerrar".
Adicionado o módulo cron para disparar mensagens quando o tempo de conversa for encerrado.
Sistema MVC finalizado.
Núcleo do sistema alterado com sucesso.
Foi adicionada uma nova pasta essencial para o sistema além das que já existiam (controller, language, model e view): config (Pasta que contém as configurações necessárias para o funcionamento do sistema e dos módulos).
Graças a alteração do Núcleo, cada módulo da Dashboard é único para cada usuário, ou seja, a dashboard é modificada conforme os módulos que forem configurados por usuário.
Adicionada validação de mais de um módulo habilitado da categoria dashboard. Se um módulo estiver habilitado, não será possível para o usuário configurar outro módulo até que o que está halitado seja desabilitado.
Adicionada opção para habilitar/desabilitar uma mensagem.
Adicionada opção para ordenar as opções dos menus do chatbot em uma mensagem de menu.
Adicionadas a categoria de módulo "Temas"
Os três módulos da Dashboard do chatbot foram atualizados com novas opções (Alteração de cor e título).
Novos módulos estão sendo desenvolvidos para a Dashboard do sistema são eles: "Gráfico dos Atendentes Mais Ativos", "Últimas Conversas", "Palavras-chave mais recentes", "Palavras-chave mais digitadas"
Novo tipo de mensagem adicionado "Mídia" (Este tipo permite o envio de imagens, vídeos, aúdios e documentos) via url.


(Março):
Sistema Multi-idioma acrescentado.
Sistema Multi-tema acrescentado.
Opção para definir qual o tempo máximo de duração da conversa após a última interação do cliente e o atendente.
Identificado erro que fazia com que vários códigos rodassem ao mesmo tempo diversas vezes. Inclusive a conexão da base de dados.
Acrescentada a categoria de módulo "Integrações".
Melhoria do sistema MVC. (Possivelmente a versão final do MVC).
Acrescentada a janela de relatórios (Em Andamento).
Foram feitas melhorias no sistema de rotas.
Classe PHPMailer instalada via composer.
Melhoria do sistema MVC e nos namespace das classes. (Possivelmente a versão final do MVC).
Melhorias do sistema de extensões (Antes o sistema identificava somente se os campos foram preenchidos, agora, ele também valida conforme regex.
Corrigido erro que foi identificado após as alterações do sistema MVC. O chatbot não estava mais identificando o início da conversa.
Corrigido erro de consulta ao banco de dados que ocorria ao cliente selecionar uma das opções do menu.
Corrigido erro que enviava o que estava cadastrado durante a conversa. Exemplo "valores", ele enviava o que estava cadastrado para o destinatário (Cliente ou atendente).
Corrigido erro que enviada duas vezes a palavra "suporte" que estava cadastrada como palavra-chave para o atendente. (Identificado após a correção do bug anterior).
Identificado erro que desvinculava as opções do menu quando alguma nova opção era adicionada.
Início do desenvolvimento da página de atendentes.
Alteração das regras de criação de senha realizada (8 caracteres, deve conter 1 número, 1 letra maiúscula, 1 letra minúscula e 1 caractere especial).

(Fevereiro):
Acrescentada a possibilidade de envio de documentos (PDF e DOCX), contatos e de localização.
Corrigido erro no admin do sistema que impedia o cadastro de mensagens interativas.
Corrigido erro no admin no cadastro utilizando aspas '"'
Corrigido erro que impedia o envio de documentos e encerrava a conversa entre o cliente e o atendente.
Acrescentada a possibilidade de identificação do nome do cliente pela variavél "[customer_name]".
Acrescentada a possibilidade de vários idiomas utilizando arquivos .XML (O padrão é o Português PT-BR)
Acrescentada a janela de configurações de módulos.
Acrescentadas as categorias de módulo "Captcha", "Dashboard", "Instaladores", "Login", "Relatórios" e "Trabalhos Cron"
Módulos "desenvolvidos" para a dashboard: "Últimas Interações", "Status das Mensagens" e "Gráfico de Mensagens"
Módulo "desenvolvido" para o captcha do login "Básico"
Melhoria no sistema MVC acrescentada utilizando o composer
Alteração do sistema MVC (Namespaces foram acrescentados para evitar conflitos com outros códigos).

(Janeiro):
Pasta app movida para a pasta "webhook" que fica dentro do admin.
Configuração de "Objeto de Requisição" da API acrescentada
Pasta admin renomeada para "app".
Nova classe criada "Log" para registros de logs e futuras manutenções.
Identificação de alguns erros.

(Dezembro):
Erro do envio de vídeos corrigido.
Identificada falha de segurança que permitia acessar o admin mesmo não ter realizado o login.

(Outubro):
Acrescentada a possilidade de interação entre o cliente e o atendente.
Alguns erros foram identificados (como o não envio de documentos e vídeos e contatos).
Acrescentada a janela da dashboard.

(Novembro):
Acrescentada a possibilidade de configuração de mensagens interativas (MENUS).
Acrescentada a possilibidade de envio de imagens, vídeos, links, figurinhas
Acrescentada a possibilidade de envio de mensagens interativas.

(Setembro):
Janela de login acrescentada.
Janela de configuração de usuários acrescentada.
Janela de configuração de integração com o WhatsApp acrescentada.
Link para desfazer o login do sistema acrescentada.
Início do desenvolvimento do sistema MVC do ChatBot.
Pastas iniciais criadas: admin (Administrador), app (Aplicativo) e system (Sistema).
Instalação do template "Twig" utilizando o composer.
Primeiras requisições e testes para a API do WhatsApp.
Acrescentada a possibilidade de envio de mensagens de texto simples.
Criação de regra básica de criação de senha (4 caracteres).

QUAIS NOVAS MUDANÇAS ESTÃO SENDO PLANEJADAS:
Desenvolvimento de novos módulos para a Dashboard do sistema: "Taxa de abandono do Chatbot", "Taxa de resolução automática", "Taxa de redirecionamento para atendimento humano", "Tempo médio de resposta do Chatbot", "Taxa de conversões (por exemplo, conversões para vendas ou leads gerados)", "Taxa de repetição de perguntas", "Taxa de solicitações de ajuda ou suporte", "Taxa de interações fora do horário de atendimento".
Desenvolvimento de novos módulos de Captcha "Captcha Matemático", "Google ReCaptcha v2", "Google ReCaptcha v3".
Desenvolvimento de sistema de recuperação de senha por e-mail e por WhatsApp.
Desenvolvimento de sistema de Histórico de Alterações nos atendentes e mensagens.
Desenvolvimento de sistema para histórico de alteração de senha.
Desenvolvimento de novo layout.
Desenvolvimento de janela popup fixa na tela do chat.
Desenvolvimento de sistema para visualização da conta do usuário (Apenas para usuário root).
Finalização da janela de relatórios.
Desenvolvimento de sistema de paginação para os atendentes, mensagens e usuários.
Desenvolvimento de sistema de filtro para os atendentes, mensagens e usuários.
Melhorias no sistema de extensões (Adicionar opções de instalar e desinstalar uma extensão no sistema assim como existe no OpenCart
Desenvolvimento de API do chatbot para integrar com o Mautic
Alteração do sistema de distribuição de atendentes para os clientes.
