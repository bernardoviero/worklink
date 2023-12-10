
# Api WORKLINK

## Passo a passo para rodar o projeto
Clone Repositório
```sh
git clone https://github.dev/especializati/laravel-10-rest-api.git app-laravel
```
```sh
cd app-laravel/
```


Crie o Arquivo .env
```sh
cp .env.example .env
```


Atualize as variáveis de ambiente do arquivo .env
```dosini
APP_NAME=EspecializaTi
APP_URL=http://localhost:8989
L5_SWAGGER_CONST_HOST=http://project.test/api/v1

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=worklink
DB_USERNAME=root
DB_PASSWORD=root

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```


Suba os containers do projeto
```sh
docker-compose up -d
```


Acessar o container
```sh
docker-compose exec app bash
```


Instalar as dependências do projeto
```sh
composer install
```


Gerar a key do projeto Laravel
```sh
php artisan key:generate
```


## Scrip SQL - Disciplina banco de dados 2:
```sql

create database WORKLINK;
USE WORKLINK;

CREATE TABLE usuarios (
  id_usuario SERIAL PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  cpf_cnpj VARCHAR(255) NOT NULL,
  senha VARCHAR(50) NOT NULL,
  cep VARCHAR(9) NOT NULL,
  cidade VARCHAR(100) NOT NULL,
  uf CHAR(2) NOT NULL,
  data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  data_alteracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  ativo INT DEFAULT 1
);

ALTER TABLE usuarios
ADD COLUMN data_nascimento DATE;

ALTER TABLE usuarios
ADD COLUMN contato varchar(20);

ALTER TABLE usuarios
ADD COLUMN genero CHAR(1),
ADD CONSTRAINT check_genero CHECK (genero IN ('M', 'F'));

CREATE TABLE tipos_trabalho (
  id_tipo_trabalho SERIAL PRIMARY KEY,
  descricao VARCHAR(255) NOT NULL,
  trabalho VARCHAR(100) NOT NULL,
  ativo INT DEFAULT 1
);

CREATE TABLE trabalhos (
  id_trabalho SERIAL PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_tipo_trabalho INT NOT NULL,
  valor VARCHAR(255) NOT NULL,
  pagamento int not null,
  data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  data_alteracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  ativo INT DEFAULT 1,
  
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
  FOREIGN KEY (id_tipo_trabalho) REFERENCES tipos_trabalho(id_tipo_trabalho)
);

create or REPLACE VIEW view_trabalhos_usuarios AS
SELECT
    u.nome,
    u.cpf_cnpj,
    tp.trabalho,
    t.valor,
    t.ativo,
    t.pagamento 
FROM
    trabalhos t
JOIN
    usuarios u ON t.id_usuario = u.id_usuario
JOIN
    tipos_trabalho tp ON t.id_tipo_trabalho = tp.id_tipo_trabalho;
   
CREATE TABLE log_usuarios (
    id_log SERIAL PRIMARY KEY,
    id_usuario INT,
    comando VARCHAR(10),
    dados_antigos JSONB,
    dados_novos JSONB,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE OR REPLACE FUNCTION log_alteracoes_usuarios()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'DELETE' THEN
        INSERT INTO log_usuarios (id_usuario, comando, dados_antigos)
        VALUES (OLD.id_usuario, 'DELETE', to_jsonb(OLD));
    ELSIF TG_OP = 'UPDATE' THEN
        INSERT INTO log_usuarios (id_usuario, comando, dados_antigos, dados_novos)
        VALUES (NEW.id_usuario, 'UPDATE', to_jsonb(OLD), to_jsonb(NEW));
    ELSIF TG_OP = 'INSERT' THEN
        INSERT INTO log_usuarios (id_usuario, comando, dados_novos)
        VALUES (NEW.id_usuario, 'INSERT', to_jsonb(NEW));
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER usuarios_trigger
AFTER INSERT OR UPDATE OR DELETE ON usuarios
FOR EACH ROW
EXECUTE FUNCTION log_alteracoes_usuarios();

CREATE TABLE log_tipos_trabalho (
    id_log SERIAL PRIMARY KEY,
    id_tipo_trabalho INT,
    comando VARCHAR(10),
    dados_antigos JSONB,
    dados_novos JSONB,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE OR REPLACE FUNCTION log_alteracoes_tipos_trabalho()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'DELETE' THEN
        INSERT INTO log_tipos_trabalho (id_tipo_trabalho, comando, dados_antigos)
        VALUES (OLD.id_tipo_trabalho, 'DELETE', to_jsonb(OLD));
    ELSIF TG_OP = 'UPDATE' THEN
        INSERT INTO log_tipos_trabalho (id_tipo_trabalho, comando, dados_antigos, dados_novos)
        VALUES (NEW.id_tipo_trabalho, 'UPDATE', to_jsonb(OLD), to_jsonb(NEW));
    ELSIF TG_OP = 'INSERT' THEN
        INSERT INTO log_tipos_trabalho (id_tipo_trabalho, comando, dados_novos)
        VALUES (NEW.id_tipo_trabalho, 'INSERT', to_jsonb(NEW));
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tipos_trabalho_trigger
AFTER INSERT OR UPDATE OR DELETE ON tipos_trabalho
FOR EACH ROW
EXECUTE FUNCTION log_alteracoes_tipos_trabalho();

CREATE TABLE log_trabalhos (
    id_log SERIAL PRIMARY KEY,
    id_trabalho INT,
    comando VARCHAR(10),
    dados_antigos JSONB,
    dados_novos JSONB,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE OR REPLACE FUNCTION log_alteracoes_trabalhos()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'DELETE' THEN
        INSERT INTO log_trabalhos (id_trabalho, comando, dados_antigos)
        VALUES (OLD.id_trabalho, 'DELETE', to_jsonb(OLD));
    ELSIF TG_OP = 'UPDATE' THEN
        INSERT INTO log_trabalhos (id_trabalho, comando, dados_antigos, dados_novos)
        VALUES (NEW.id_trabalho, 'UPDATE', to_jsonb(OLD), to_jsonb(NEW));
    ELSIF TG_OP = 'INSERT' THEN
        INSERT INTO log_trabalhos (id_trabalho, comando, dados_novos)
        VALUES (NEW.id_trabalho, 'INSERT', to_jsonb(NEW));
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trabalhos_trigger
AFTER INSERT OR UPDATE OR DELETE ON trabalhos
FOR EACH ROW
EXECUTE FUNCTION log_alteracoes_trabalhos();

CREATE TABLE evolucao_valor_trabalho (
    id_evolucao SERIAL PRIMARY KEY,
    id_trabalho INT,
    id_usuario INT,
    novo_valor_cobrado VARCHAR(255) NOT NULL,
    data_modificacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_trabalho) REFERENCES trabalhos(id_trabalho)
);


CREATE OR REPLACE FUNCTION log_evolucao_valor_trabalho()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'UPDATE' AND NEW.valor <> OLD.valor THEN
        INSERT INTO evolucao_valor_trabalho (id_trabalho, novo_valor_cobrado, id_usuario)
        VALUES (NEW.id_trabalho, NEW.valor, NEW.id_usuario);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER evolucao_valor_trabalho_trigger
AFTER UPDATE ON trabalhos
FOR EACH ROW
EXECUTE FUNCTION log_evolucao_valor_trabalho();


insert into usuarios values(1,'Bernardo Viero', 'b@gmail.com','03899889031','senha123','97070610','Santa Maria','RS'),
(2,'Mauricio Pereira', 'm@teste.com','04100032658','senha321','97070530','Santa Maria','RS'),
(3,'Herysson Figueiredo', 'h@teste.com','02155820146','senha123321','97070180','Santa Maria','RS'),
(4,'Juca Ralho', 'j@teste.com','01122654071','senha','97050200','Santa Maria','RS');

insert into tipos_trabalho values(1,'Serviço de casa','Serviço Doméstico'),
(2,'Manutenção de equipamentos','Manutenção e Reparos'),
(3,'Construção de moradias','Construção e Reforma'),
(4,'Jardinagem e manutenção de áreas rurais','Jardinagem e Paisagismo'),
(5,'Arquitetura e montagem de Festas','Eventos e Hospitalidade'),
(6,'Suporte e reparo a computadores e celulares','Assistência Técnica e Tecnologia'),
(7,'Fretes','Transporte e Mudanças'),
(8,'Aulas educacionais e treinamentos físicos','Educação e Treinamento'),
(9,'Aulas nutricionais e atendimentos de saúde','Saúde e Bem-Estar'),
(10,'Consultoria para ajudar seu negócio','Consultoria e Serviços Profissionais');

```

Acessar o projeto
[http://localhost:8989](http://localhost:8989)
