# Api-Migrations

Esta aplicação é responsável pelo processamento de duplicação de lojas na plataforma Bagy.

## Tecnologias Utilizadas

A aplicação utiliza as seguintes tecnologias e serviços:
- **PHP 8.3** (Rodando no contêiner `api-migrations-php`)
- **Laravel** 12.0
- **MongoDB** (Banco de dados principal)
- **Nginx** (Servidor web)
- **Docker** e **Docker Compose** (Gerenciamento de contêineres)

## Endpoints

### Migrar uma loja bagy para bagy

**Método:** `POST`

**URL:** `{url}/api/migrations`

**Body:**
```json
{
  "fromToken": "seu_token_origem",
  "toToken": "seu_token_destino"
}
```

### Importação de loja externa

**Método:** `POST`

**URL:** `{url}/api/shop-importation`

**form-data:**
```
token: "seu_token_destino",
resource: "sua_entidade",
file: file.csv
```

Installation
Start the Docker containers:
```sh
docker-compose up -d
```

Access the Docker container's shell:
```sh
docker-compose exec api-migrations-php bash
```

Copy and Paste .env.example as .env
```sh
Copy the contents of .env.example and create a new file named .env. Then, paste the copied contents into .env. Ensure to adjust the variables according to your environment.
```

Install dependencies using Composer:
```sh
composer install
```

Run Queue:
```sh
php artisan queue:work
php artisan queue:work --queue=shop-importation
```

- Contact
For any inquiries or support, please feel free to contact:
```sh
Pablo Fabrício - fabriciopablo2000@gmail.com
```
