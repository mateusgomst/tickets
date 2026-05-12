# RDoar

API Laravel do projeto RDoar.

## Requisitos

- PHP 8.4 com extensoes: `cli`, `fpm`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `intl`, `pgsql`, `redis`, `gd`, `opcache` e `readline`
- Composer
- Node.js LTS e npm
- PostgreSQL
- Redis
- Nginx
- Supervisor, para workers em homologacao/producao

## Infraestrutura

### PHP 8.4

```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update

sudo apt-get install -y \
  software-properties-common \
  ca-certificates \
  lsb-release \
  apt-transport-https \
  curl \
  zip \
  unzip \
  git \
  nginx \
  php8.4 \
  php8.4-cli \
  php8.4-fpm \
  php8.4-mbstring \
  php8.4-xml \
  php8.4-curl \
  php8.4-zip \
  php8.4-bcmath \
  php8.4-intl \
  php8.4-pgsql \
  php8.4-redis \
  php8.4-gd \
  php8.4-opcache \
  php8.4-readline
```

Em ambiente de desenvolvimento, instale tambem se precisar:

```bash
sudo apt-get install -y php8.4-dev php8.4-xdebug
```

> Nao instale `php8.4-dev` e `php8.4-xdebug` em producao.

### Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

## Instalacao

### Clonar o projeto

```bash
cd /var/www
git clone <URL_DO_REPOSITORIO> rdoar
cd rdoar
```

### Dependencias PHP

Desenvolvimento:

```bash
composer install
```

Homologacao/producao:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
```

Verifique a instalacao:

```bash
php artisan about
```

### Ambiente

```bash
cp .env.example .env
nano .env
php artisan key:generate
```

Variaveis principais:

| Variavel | Descricao | Exemplo |
| --- | --- | --- |
| `APP_NAME` | Nome da aplicacao | `RDoar` |
| `APP_ENV` | Ambiente de execucao | `local`, `production` |
| `APP_DEBUG` | Debug da aplicacao | `true`, `false` |
| `APP_URL` | URL base da aplicacao | `https://rdoar.local` |
| `DB_CONNECTION` | Driver do banco | `postgres` |
| `DB_HOST` | Host do PostgreSQL | `127.0.0.1` |
| `DB_PORT` | Porta do PostgreSQL | `5432` |
| `DB_DATABASE` | Nome do banco | `rdoar` |
| `DB_USERNAME` | Usuario do banco | `postgres` |
| `DB_PASSWORD` | Senha do banco | `postgres` |
| `QUEUE_CONNECTION` | Driver de filas | `redis` |
| `CACHE_STORE` | Driver de cache | `redis` |
| `BROADCAST_CONNECTION` | Broadcast | `null` |

### Banco de dados

Crie o banco PostgreSQL:

```bash
sudo -u postgres psql -c "CREATE DATABASE rdoar;"
```

Execute as migrations:

```bash
php artisan migrate
```

Em desenvolvimento, para recriar o banco do zero:

```bash
php artisan migrate:fresh
```

### Passport

O projeto usa Laravel Passport para emissao de tokens.

```bash
php artisan passport:keys
```

Se precisar criar um client OAuth:

```bash
php artisan passport:client
```

### Permissoes

```bash
sudo usermod -aG www-data $USER
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R 2775 storage bootstrap/cache
```

Se as chaves do Passport estiverem em `storage/`, restrinja as permissoes:

```bash
sudo chmod -R 660 storage/oauth-*
```

### Frontend/assets

```bash
npm install
npm run build
```

Para desenvolvimento com Vite:

```bash
npm run dev
```

## Executando em desenvolvimento

```bash
php artisan serve
```

Em outro terminal, execute as filas:

```bash
php artisan queue:work redis --queue=default
```

Tambem existe um script Composer que sobe servidor, fila, logs e Vite em conjunto:

```bash
composer run dev
```

## Nginx

Este repositorio possui um exemplo em `nginx.conf`.

Para instalar:

```bash
sudo cp nginx.conf /etc/nginx/sites-available/rdoar
sudo ln -s /etc/nginx/sites-available/rdoar /etc/nginx/sites-enabled/rdoar
sudo nginx -t
sudo service nginx restart
```

Atualize no arquivo copiado:

- `server_name`
- `root`
- caminhos dos certificados SSL
- versao/socket do PHP-FPM, se necessario

Para HTTPS local, uma opcao e usar `mkcert`:

```bash
sudo apt install -y libnss3-tools mkcert
sudo mkdir -p /etc/nginx/ssl
sudo chown $USER:$USER /etc/nginx/ssl
cd /etc/nginx/ssl
mkcert rdoar.local '*.rdoar.local'
```

## Supervisor

Use Supervisor em homologacao/producao para manter as filas ativas.

Exemplo de programa:

```ini
[program:rdoar-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/rdoar/artisan queue:work redis --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/rdoar/storage/logs/worker.log
```

Depois de criar o arquivo em `/etc/supervisor/conf.d/rdoar.conf`:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart rdoar-worker:*
sudo supervisorctl status
```

## API

Rotas principais disponiveis hoje:

| Metodo | Rota | Descricao |
| --- | --- | --- |
| `POST` | `/api/auth` | Autentica usuario e retorna token Bearer |
| `GET` | `/api/test` | Verifica se a API esta respondendo |
| `GET` | `/api/me` | Retorna usuario autenticado |

Exemplo de login:

```bash
curl -X POST https://rdoar.local/api/auth \
  -H "Content-Type: application/json" \
  -d '{"email":"usuario@example.com","password":"senha"}'
```

## Ferramentas de desenvolvimento

Formatacao:

```bash
./vendor/bin/pint
```

Testes:

```bash
php artisan test
```

Build dos assets:

```bash
npm run build
```
