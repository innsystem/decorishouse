# Bot WhatsApp da Decoris House

Este documento fornece as instruções necessárias para configurar o bot WhatsApp da Decoris House utilizando TypeBot, N8N e Evolution API.

## 1. Melhorias na API

Foram adicionadas as seguintes melhorias na API Laravel existente:

### Novas Rotas:
- `/api/products/recent` - Retorna os produtos mais recentes
- `/api/products/promotions` - Retorna os produtos em promoção
- `/api/products/search?query=termo` - Pesquisa produtos por nome (já existia, mas foi melhorada)

### Modificações no ProductService:
- Melhoria no método `searchProducts()` para retornar mais informações
- Adição do método `getRecentProducts()` para buscar produtos recentes
- Adição do método `getPromotionalProducts()` para buscar produtos em promoção

## 2. Configuração do TypeBot

### Importação do Fluxo
1. Acesse sua conta no TypeBot (https://app.typebot.io/)
2. Clique em "Create" ou "New Typebot"
3. Selecione "Import" e carregue o arquivo `decorishouse_bot_flow.json`
4. Ajuste quaisquer configurações necessárias (URLs, variáveis específicas do ambiente, etc.)

### Estrutura do Fluxo
O bot possui a seguinte estrutura:

1. **Boas-vindas**
   - Solicita o nome do usuário
   - Armazena o nome na variável `Name`

2. **Salvar Número**
   - Salva o número do WhatsApp na variável `telefone_cliente`
   - Exibe uma mensagem de boas-vindas personalizada

3. **Menu Principal**
   - Oferece três opções: Ver Promoções Recentes, Pesquisar Produto, Falar com Atendente

4. **Promoções Recentes**
   - Faz um requisição à API `/api/products/promotions`
   - Formata e exibe os resultados com preços, descontos e links
   - Permite voltar ao menu principal

5. **Pesquisa de Produto**
   - Solicita o termo de pesquisa
   - Valida se o termo tem pelo menos 3 caracteres
   - Faz requisição à API `/api/products/search`
   - Formata e exibe os resultados
   - Permite fazer nova pesquisa ou voltar ao menu principal

6. **Falar com Atendente**
   - Exibe mensagem de transferência
   - Armazena dados do cliente (nome e telefone) via webhook
   - Transfere o chat para atendimento humano

### Variáveis do Bot
- `Name`: Nome do cliente
- `telefone_cliente`: Número de WhatsApp do cliente
- `termo_pesquisa`: Termo de pesquisa informado pelo cliente
- `resultados_promocoes`: Resultados da API de promoções
- `resultados_pesquisa`: Resultados da pesquisa de produtos
- `mensagem_promocoes`: Mensagem formatada com promoções
- `mensagem_pesquisa`: Mensagem formatada com resultados da pesquisa
- `validacao_pesquisa`: Objeto com validação do termo de pesquisa
- `whatsapp_number`: Número do WhatsApp (variável de sessão)

## 3. Configuração do N8N (opcional)

Se preferir usar o N8N para processar os dados antes de enviar para o TypeBot, configure os seguintes workflows:

### Workflow 1: Processar Promoções
1. Crie um novo workflow no N8N
2. Adicione um trigger HTTP (Webhook)
3. Adicione um nó HTTP Request para `/api/products/promotions`
4. Adicione um nó Function para formatar os dados
5. Conecte a resposta de volta ao TypeBot

### Workflow 2: Processar Pesquisa
1. Crie um novo workflow no N8N
2. Adicione um trigger HTTP (Webhook)
3. Adicione um nó HTTP Request para `/api/products/search`
4. Adicione um nó Function para formatar os dados
5. Conecte a resposta de volta ao TypeBot

## 4. Configuração da Evolution API (WhatsApp)

### Configuração no Evolution API
1. Acesse o painel da Evolution API
2. Configure uma nova instância
3. Configure o webhook para apontar para o TypeBot
4. Escaneie o código QR para conectar o número do WhatsApp

### Configuração no TypeBot
1. Vá para "Settings" > "WhatsApp"
2. Ative a integração com WhatsApp
3. Configure para usar a Evolution API
4. Adicione os dados da sua instância
5. Teste a conexão

## 5. Testando o Bot

Para testar o bot completo:

1. Certifique-se de que a API está funcionando corretamente testando os endpoints:
   - `https://decorishouse.com.br/api/products/recent`
   - `https://decorishouse.com.br/api/products/promotions`
   - `https://decorishouse.com.br/api/products/search?query=mesa`

2. Publique seu bot no TypeBot

3. Conecte o WhatsApp através da Evolution API

4. Teste a conversa seguindo este fluxo:
   - Início: Bot pergunta o nome
   - Responda com seu nome
   - Bot exibe o menu principal
   - Teste cada uma das opções
   - Verifique se os resultados da API são exibidos corretamente
   - Verifique se os links dos produtos funcionam

## Notas Adicionais

- Para personalizar ainda mais o bot, você pode ajustar as mensagens e formatação no TypeBot.
- Para melhorar a experiência do usuário, considere adicionar imagens dos produtos nas mensagens.
- A webhook para armazenamento de dados do cliente no fluxo "Falar com Atendente" precisa ser configurado para seu sistema específico.

## Suporte

Em caso de dúvidas ou problemas, entre em contato com a equipe de desenvolvimento. 