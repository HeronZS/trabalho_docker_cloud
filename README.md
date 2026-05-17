# 📦 StockFlow — Sistema de Gestão de Estoque

> Aplicação web para controle de estoque de uma loja, desenvolvida com PHP puro + MySQL, executada em ambiente conteinerizado com Docker e Docker Compose.

---

## 📋 Descrição da Aplicação

O **StockFlow** é um sistema de gestão de estoque que permite:

- **Cadastrar e consultar produtos** com nome, descrição, preço, quantidade e código de barras
- **Gerenciar categorias** de produtos
- **Registrar movimentações** de entrada e saída de estoque
- **Visualizar alertas** de estoque baixo ou zerado
- **Acompanhar o dashboard** com KPIs em tempo real

---

## 🛠 Tecnologias Utilizadas

| Tecnologia       | Versão  | Função                          |
|------------------|---------|---------------------------------|
| PHP              | 8.2     | Linguagem back-end              |
| Apache           | 2.4     | Servidor web                    |
| MySQL            | 8.0     | Banco de dados relacional       |
| Tailwind CSS     | CDN     | Estilização da interface        |
| Docker           | 24+     | Conteinerização                 |
| Docker Compose   | v3.9    | Orquestração dos containers     |

---

## 🏗 Arquitetura

```
┌─────────────────────────────────────────┐
│           stockflow_net (bridge)        │
│                                         │
│  ┌──────────────────┐  ┌─────────────┐  │
│  │  stockflow_app   │  │ stockflow_db│  │
│  │  PHP 8.2+Apache  │◄─►│  MySQL 8.0 │  │
│  │  porta: 8080     │  │  porta: 3306│  │
│  └──────────────────┘  └─────────────┘  │
│           │                    │        │
│      app_logs              db_data      │
│       (volume)             (volume)     │
└─────────────────────────────────────────┘
```

**Containers:**
- `stockflow_app` — aplicação PHP servida pelo Apache
- `stockflow_db` — banco de dados MySQL com persistência via volume

**Redes:**
- `stockflow_net` — rede bridge isolada para comunicação interna

**Volumes:**
- `db_data` — persistência dos dados do MySQL
- `app_logs` — logs do Apache

---

## 📁 Estrutura do Projeto

```
projeto/
├── app/
│   ├── includes/
│   │   ├── db.php          # Conexão PDO com o banco
│   │   ├── setup.php       # Criação das tabelas e seed inicial
│   │   ├── functions.php   # Funções auxiliares
│   │   ├── header.php      # Layout base (topo)
│   │   └── footer.php      # Layout base (rodapé)
│   ├── pages/
│   │   ├── produtos.php    # CRUD de produtos
│   │   ├── categorias.php  # CRUD de categorias
│   │   └── movimentacoes.php # Entradas e saídas
│   └── index.php           # Dashboard principal
├── evidencias/             # Capturas de tela da aplicação
├── Dockerfile              # Imagem da aplicação
├── docker-compose.yml      # Orquestração dos containers
├── .env                    # Variáveis de ambiente
└── README.md               # Este arquivo
```

---

## ⚙️ Variáveis de Ambiente

| Variável          | Padrão           | Descrição                        |
|-------------------|------------------|----------------------------------|
| `APP_PORT`        | `8080`           | Porta de acesso no host          |
| `DB_NAME`         | `estoque`        | Nome do banco de dados           |
| `DB_USER`         | `stockflow`      | Usuário do banco                 |
| `DB_PASS`         | `stockflow@2024` | Senha do usuário                 |
| `DB_ROOT_PASSWORD`| `root@2024`      | Senha do root do MySQL           |

---

## 🚀 Instruções de Execução

### Pré-requisitos

- [Docker](https://docs.docker.com/get-docker/) instalado
- [Docker Compose](https://docs.docker.com/compose/install/) instalado
- Portas `8080` e `3306` disponíveis no host

### Passo a passo

**1. Clone o repositório**
```bash
git clone https://github.com/SEU_USUARIO/stockflow.git
cd stockflow
```

**2. Configure as variáveis de ambiente**
```bash
# O arquivo .env já está configurado com valores padrão
# Edite se necessário:
nano .env
```

**3. Suba os containers**
```bash
docker compose up -d
```

**4. Acesse a aplicação**

Abra o navegador e acesse:
```
http://localhost:8080
```

> ⏳ Na primeira execução, aguarde ~20 segundos enquanto o MySQL inicializa. A aplicação cria as tabelas e dados de exemplo automaticamente.

---

## 🧪 Comandos Úteis

```bash
# Subir os containers em background
docker compose up -d

# Ver logs em tempo real
docker compose logs -f

# Ver logs apenas da aplicação
docker compose logs -f app

# Ver logs apenas do banco
docker compose logs -f db

# Parar os containers
docker compose down

# Parar e remover volumes (apaga os dados)
docker compose down -v

# Reconstruir a imagem após alterações
docker compose up -d --build

# Acessar o shell do container da aplicação
docker exec -it stockflow_app bash

# Acessar o MySQL diretamente
docker exec -it stockflow_db mysql -u stockflow -pstockflow@2024 estoque
```

---

## 🔌 Portas Utilizadas

| Serviço     | Container | Host   |
|-------------|-----------|--------|
| Aplicação   | 80        | 8080   |
| MySQL       | 3306      | —      |

> O MySQL não expõe porta ao host por segurança. A comunicação ocorre apenas internamente via rede Docker (`stockflow_net`).

---

## ✅ Validação da Aplicação

Após executar `docker compose up -d`, o professor pode validar:

| Item                         | Como validar                                              |
|------------------------------|-----------------------------------------------------------|
| Containers rodando           | `docker compose ps`                                       |
| Acesso à aplicação           | Abrir `http://localhost:8080`                             |
| Conexão com banco            | Dashboard carrega com dados — tabelas criadas via setup   |
| Cadastro de produto          | Menu Produtos → preencher formulário → Cadastrar          |
| Consulta de produto          | Menu Produtos → campo de busca                            |
| Movimentação de estoque      | Menu Movimentações → registrar entrada/saída              |
| Persistência de dados        | `docker compose down && docker compose up -d` → dados mantidos |
| Rede entre containers        | `docker network inspect projeto_stockflow_net`            |
| Volumes                      | `docker volume ls`                                        |

---

## 🐳 Docker Hub

A imagem da aplicação está publicada no Docker Hub:

```
docker pull SEU_USUARIO/stockflow:latest
```

Para rodar apenas com a imagem publicada, substitua o bloco `build` do `docker-compose.yml` por:
```yaml
image: SEU_USUARIO/stockflow:latest
```

---

## 👤 Autor

**Seu Nome**  
Disciplina: Cloud Computing e DevOps  
Curso: [Seu Curso]  
Instituição: [Sua Instituição]
