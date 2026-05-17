# StockFlow — Sistema de Gestao de Estoque

Aplicacao web para controle de estoque de uma loja, desenvolvida com PHP puro e MySQL, executada em ambiente conteinerizado com Docker e Docker Compose, hospedada na AWS Academy (EC2).

---

## Descricao da Aplicacao

O StockFlow e um sistema de gestao de estoque que permite:

- Cadastrar e consultar produtos com nome, descricao, preco, quantidade e codigo de barras
- Gerenciar categorias de produtos
- Registrar movimentacoes de entrada e saida de estoque
- Visualizar alertas de estoque baixo ou zerado
- Acompanhar o dashboard com KPIs em tempo real

---

## Tecnologias Utilizadas

| Tecnologia     | Versao | Funcao                      |
|----------------|--------|-----------------------------|
| PHP            | 8.2    | Linguagem back-end          |
| Apache         | 2.4    | Servidor web                |
| MySQL          | 8.0    | Banco de dados relacional   |
| Tailwind CSS   | CDN    | Estilizacao da interface    |
| Docker         | 29+    | Conteinerizacao             |
| Docker Compose | v5+    | Orquestracao dos containers |
| AWS EC2        | —      | Hospedagem na nuvem         |

---

## Arquitetura

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

Containers:
- stockflow_app — aplicacao PHP servida pelo Apache
- stockflow_db — banco de dados MySQL com persistencia via volume

Redes:
- stockflow_net — rede bridge isolada para comunicacao interna entre os containers

Volumes:
- db_data — persistencia dos dados do MySQL
- app_logs — logs do Apache

---

## Estrutura do Projeto

```
projeto/
├── app/
│   ├── includes/
│   │   ├── db.php             # Conexao PDO com o banco
│   │   ├── setup.php          # Criacao das tabelas e seed inicial
│   │   ├── functions.php      # Funcoes auxiliares
│   │   ├── header.php         # Layout base (topo)
│   │   └── footer.php         # Layout base (rodape)
│   ├── pages/
│   │   ├── produtos.php       # CRUD de produtos
│   │   ├── categorias.php     # CRUD de categorias
│   │   └── movimentacoes.php  # Entradas e saidas
│   └── index.php              # Dashboard principal
├── evidencias/                # Capturas de tela da aplicacao
├── Dockerfile                 # Imagem da aplicacao
├── docker-compose.yml         # Orquestracao dos containers
├── .env                       # Variaveis de ambiente
└── README.md                  # Este arquivo
```

---

## Variaveis de Ambiente

| Variavel           | Padrao           | Descricao                  |
|--------------------|------------------|----------------------------|
| APP_PORT           | 8080             | Porta de acesso no host    |
| DB_NAME            | estoque          | Nome do banco de dados     |
| DB_USER            | stockflow        | Usuario do banco           |
| DB_PASS            | stockflow@2024   | Senha do usuario           |
| DB_ROOT_PASSWORD   | root@2024        | Senha do root do MySQL     |

---

## Instrucoes de Execucao

### Pre-requisitos

- Docker instalado
- Docker Compose instalado
- Porta 8080 disponivel no host
- Caso utilize AWS EC2, liberar a porta 8080 no Security Group

### Passo a passo

1. Clone o repositorio

```bash
git clone https://github.com/heronzonta/stockflow.git
cd stockflow
```

2. Suba os containers

```bash
docker compose up -d
```

3. Acesse a aplicacao

Abra o navegador e acesse:

```
http://localhost:8080
```

Na primeira execucao, aguarde aproximadamente 20 segundos enquanto o MySQL inicializa. A aplicacao cria as tabelas e os dados de exemplo automaticamente.

---

## Comandos Uteis

```bash
# Subir os containers em background
docker compose up -d

# Ver status dos containers
docker compose ps

# Ver logs em tempo real
docker compose logs -f

# Ver logs apenas da aplicacao
docker compose logs -f app

# Ver logs apenas do banco
docker compose logs -f db

# Parar os containers
docker compose down

# Parar e remover volumes (apaga os dados)
docker compose down -v

# Reconstruir a imagem apos alteracoes
docker compose up -d --build

# Acessar o shell do container da aplicacao
docker exec -it stockflow_app bash

# Acessar o MySQL diretamente
docker exec -it stockflow_db mysql -u stockflow -pstockflow@2024 estoque
```

---

## Portas Utilizadas

| Servico    | Container | Host |
|------------|-----------|------|
| Aplicacao  | 80        | 8080 |
| MySQL      | 3306      | —    |

O MySQL nao expoe porta ao host por seguranca. A comunicacao ocorre apenas internamente via rede Docker (stockflow_net).

---

## Validacao da Aplicacao

Apos executar docker compose up -d, o professor pode validar:

| Item                    | Como validar                                                    |
|-------------------------|-----------------------------------------------------------------|
| Containers rodando      | docker compose ps                                               |
| Acesso a aplicacao      | Abrir http://localhost:8080                                     |
| Conexao com banco       | Dashboard carrega com dados — tabelas criadas via setup         |
| Cadastro de produto     | Menu Produtos — preencher formulario — Cadastrar                |
| Consulta de produto     | Menu Produtos — campo de busca                                  |
| Movimentacao de estoque | Menu Movimentacoes — registrar entrada ou saida                 |
| Persistencia de dados   | docker compose down seguido de docker compose up -d — dados mantidos |
| Rede entre containers   | docker network inspect projeto_stockflow_net                    |
| Volumes                 | docker volume ls                                                |

---

## Docker Hub

A imagem da aplicacao esta publicada no Docker Hub:

```bash
docker pull heronzonta/stockflow:latest
```

Para rodar apenas com a imagem publicada, substitua o bloco build do docker-compose.yml por:

```yaml
image: heronzonta/stockflow:latest
```

---

## Autor

Heron Zonta
Disciplina: Cloud Computing
Curso: Sistemas de Informação
Instituicao: UNIDAVI
