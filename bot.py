import logging
import requests
from telegram import Update, InlineKeyboardButton, InlineKeyboardMarkup
from telegram.ext import Application, MessageHandler, CommandHandler, CallbackQueryHandler, filters, CallbackContext
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.common.by import By
import time

# 🔹 Token do Bot Telegram
TELEGRAM_BOT_TOKEN = "7834900941:AAGOS_5kLAWA6z7Heh4veSFKP7o1jWSfXGg"

# 🔹 URL da API do Dashboard
DASHBOARD_API_URL = "https://decorishouse.com.br/api/products"

# 🔹 Configuração do Logging
logging.basicConfig(format="%(asctime)s - %(name)s - %(levelname)s - %(message)s", level=logging.INFO)

async def extrair_dados_shopee(url):
    """Extrai título e imagem de um link da Shopee usando Selenium"""
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--window-size=375,812")
    options.add_argument("user-agent=Mozilla/5.0 (Linux; Android 10) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36")

    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)

    try:
        driver.get(url)
        time.sleep(5)  # Espera o JavaScript carregar os dados

        titulo_element = driver.find_element(By.CSS_SELECTOR, 'meta[property="og:title"]')
        titulo = titulo_element.get_attribute("content") if titulo_element else "Título não encontrado"

        imagem_element = driver.find_element(By.CSS_SELECTOR, 'meta[property="og:image"]')
        imagem_url = imagem_element.get_attribute("content") if imagem_element else "Imagem não encontrada"

        return {"titulo": titulo, "imagem": imagem_url, "link": url}
    
    except Exception as e:
        print(f"Erro ao extrair dados: {e}")
        return None
    
    finally:
        driver.quit()

async def cadastrar_no_dashboard(produto, categoria):
    """Envia os dados do produto para o Dashboard"""

    product_title = produto["titulo"].replace(" | Shopee Brasil", "")

    payload = {
        "name": product_title,
        "slug": product_title.lower().replace(" ", "-"),
        "images": produto["imagem"],
        "price": "0.00",
        "price_promotion": "0.00",
        "status": "1",
        "categories": [categoria],  # Categoria dinâmica
        "marketplace": [3],
        "affiliate_links": [produto["link"]]
    }

    response = requests.post(DASHBOARD_API_URL, json=payload, verify=False, timeout=10)

    return response.status_code == 201  # Retorna True se cadastrado com sucesso

async def handle_message(update: Update, context: CallbackContext) -> None:
    """Manipula mensagens recebidas no Telegram"""
    message_text = update.message.text.strip()

    # Dividindo a mensagem em partes
    parts = message_text.split(" ", 1)

    # Verifica se a primeira parte é um número (ID da categoria)
    if parts[0].isdigit():
        categoria = int(parts[0])  # Converte para inteiro
        link = parts[1] if len(parts) > 1 else None
    else:
        categoria = 1  # Categoria padrão se não tiver número
        link = message_text

    # Verifica se a mensagem contém um link da Shopee
    if link and "shopee" in link:
        await update.message.reply_text("🔍 Obtendo informações do produto...")

        produto = await extrair_dados_shopee(link)

        if produto:
            # Envia para o Dashboard com a categoria dinâmica
            sucesso = await cadastrar_no_dashboard(produto, categoria)

            # Retorna mensagem ao usuário
            if sucesso:
                await update.message.reply_text(
                    f"✅ Produto cadastrado com sucesso!\n\n📌 *{produto['titulo']}*\n📂 Categoria: `{categoria}`",
                    parse_mode="Markdown"
                )
            else:
                await update.message.reply_text("❌ Erro ao cadastrar o produto no dashboard.")
        else:
            await update.message.reply_text("❌ Não consegui obter informações do produto.")
    else:
        await update.message.reply_text("⚠️ Envie um link válido da Shopee.")

async def buscar_produtos(termo_busca=None, categoria=None):
    """Busca produtos no dashboard"""
    params = {}
    if termo_busca:
        params['query'] = termo_busca
    if categoria:
        params['category'] = categoria

    url = f"{DASHBOARD_API_URL}/search"
    print(f"Fazendo requisição para: {url} com parâmetros: {params}")
    
    try:
        response = requests.get(url, params=params, verify=False)
        print(f"Status code: {response.status_code}")
        print(f"Resposta: {response.text}")
        
        if response.status_code == 200:
            return response.json()
        elif response.status_code == 400:
            # Trata o erro de termo muito curto
            error_data = response.json()
            print(f"Erro na requisição: {response.status_code} - {error_data}")
            return []
        else:
            print(f"Erro na requisição: {response.status_code} - {response.text}")
            return None
    except Exception as e:
        print(f"Exceção ao buscar produtos: {e}")
        return None

async def atualizar_produto(produto_id, dados):
    """Atualiza informações de um produto"""
    url = f"{DASHBOARD_API_URL}/{produto_id}"
    response = requests.patch(url, json=dados, verify=False)
    return response.status_code == 200

async def deletar_produto(produto_id):
    """Remove um produto"""
    url = f"{DASHBOARD_API_URL}/{produto_id}"
    response = requests.delete(url, verify=False)
    return response.status_code == 200

async def handle_search(update: Update, context: CallbackContext) -> None:
    """Manipula comando de busca"""
    if not context.args:
        await update.message.reply_text("⚠️ Por favor, forneça um termo para busca.\nExemplo: /buscar mesa")
        return

    termo_busca = " ".join(context.args)
    
    # Verifica se o termo de busca tem pelo menos 3 caracteres
    if len(termo_busca) < 3:
        await update.message.reply_text("⚠️ O termo de busca deve ter pelo menos 3 caracteres.")
        return
    
    await update.message.reply_text(f"🔍 Buscando produtos com o termo: '{termo_busca}'...")
    
    produtos = await buscar_produtos(termo_busca)
    
    # Adiciona log para depuração
    print(f"Resultado da busca para '{termo_busca}': {produtos}")
    
    # Verifica se produtos é None ou vazio
    if produtos is None:
        await update.message.reply_text("❌ Erro ao buscar produtos. Tente novamente mais tarde.")
        return
    
    if len(produtos) == 0:
        await update.message.reply_text(f"❌ Nenhum produto encontrado para o termo '{termo_busca}'.")
        return
    
    mensagem = "🔍 Resultados encontrados:\n\n"
    for produto in produtos[:5]:  # Limita a 5 resultados
        mensagem += f"📦 *{produto['name']}*\n"
        mensagem += f"🔗 Link: {produto.get('affiliate_link', 'N/A')}\n\n"
    
    keyboard = [
        [InlineKeyboardButton("Ver mais", callback_data=f"mais_{termo_busca}")]
    ]
    reply_markup = InlineKeyboardMarkup(keyboard)
    
    await update.message.reply_text(
        mensagem,
        parse_mode="Markdown",
        reply_markup=reply_markup
    )

async def handle_update(update: Update, context: CallbackContext) -> None:
    """Manipula comando de atualização"""
    if len(context.args) < 2:
        await update.message.reply_text(
            "⚠️ Formato: /atualizar ID PRODUTO CAMPO VALOR\n"
            "Exemplo: /atualizar 123 price 199.99"
        )
        return

    produto_id = context.args[0]
    campo = context.args[1]
    valor = context.args[2]

    dados = {campo: valor}
    sucesso = await atualizar_produto(produto_id, dados)

    if sucesso:
        await update.message.reply_text(f"✅ Produto {produto_id} atualizado com sucesso!")
    else:
        await update.message.reply_text("❌ Erro ao atualizar o produto.")

async def handle_callback(update: Update, context: CallbackContext) -> None:
    """Manipula callbacks de botões inline"""
    query = update.callback_query
    await query.answer()
    
    data = query.data
    
    if data.startswith("mais_"):
        termo_busca = data[5:]  # Remove o prefixo "mais_"
        produtos = await buscar_produtos(termo_busca)
        
        if produtos is None:
            await query.edit_message_text(
                text="❌ Erro ao buscar mais produtos. Tente novamente mais tarde.",
                parse_mode="Markdown"
            )
            return
            
        if len(produtos) <= 5:
            await query.edit_message_text(
                text="Não há mais resultados para mostrar.",
                parse_mode="Markdown"
            )
            return
            
        mensagem = "🔍 Mais resultados encontrados:\n\n"
        for produto in produtos[5:10]:  # Mostra os próximos 5 resultados
            mensagem += f"📦 *{produto['name']}*\n"
            mensagem += f"🔗 Link: {produto.get('affiliate_link', 'N/A')}\n\n"
        
        await query.edit_message_text(
            text=mensagem,
            parse_mode="Markdown"
        )

def main():
    """Inicia o Bot"""
    app = Application.builder().token(TELEGRAM_BOT_TOKEN).build()

    # Handlers existentes
    app.add_handler(MessageHandler(filters.TEXT & ~filters.COMMAND, handle_message))
    
    # Novos handlers
    app.add_handler(CommandHandler("buscar", handle_search))
    app.add_handler(CommandHandler("atualizar", handle_update))
    app.add_handler(CallbackQueryHandler(handle_callback))

    # Inicia o bot
    print("🤖 Bot iniciado!")
    app.run_polling()

if __name__ == "__main__":
    main()
