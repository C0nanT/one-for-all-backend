.PHONY: sh-app sh-nginx sh-postgres sh fix-permissions up logs backup-db restore-db

# Diretório dos arquivos de backup (ex.: make backup-db BACKUP_DIR=/backups)
BACKUP_DIR ?= $(CURDIR)/backups
BACKUP_FILE ?= $(BACKUP_DIR)/backup-20260401-075752.dump

# Corrige permissões de storage e bootstrap/cache (editável no host; aplicado também no entrypoint ao subir)
fix-permissions:
	docker compose exec app sh -c 'chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && chmod -R 777 /var/www/storage /var/www/bootstrap/cache'

sh:
	docker compose exec app sh

sh-nginx:
	docker compose exec nginx sh

sh-postgres:
	docker compose exec postgres sh

up:
	docker compose up -d

migrate:
	docker compose exec app php artisan migrate

down:
	docker compose down

logs:
	docker logs -f ofa-app

# Dump do PostgreSQL via container (usa POSTGRES_* do .env do compose)
backup-db:
	mkdir -p $(BACKUP_DIR)
	@F="$(BACKUP_DIR)/backup-$$(date +%Y%m%d-%H%M%S).dump"; \
	docker compose exec -T postgres sh -c 'pg_dump -Fc -U "$$POSTGRES_USER" -d "$$POSTGRES_DB"' > "$$F"; \
	echo "Backup criado em $$F"

# Restaura dump PostgreSQL no banco atual (ex.: make restore-db BACKUP_FILE=/caminho/arquivo.dump)
restore-db:
	@test -f "$(BACKUP_FILE)" || (echo "Arquivo de backup não encontrado: $(BACKUP_FILE)"; exit 1)
	docker compose exec -T postgres sh -c 'pg_restore --clean --if-exists --no-owner --no-privileges -U "$$POSTGRES_USER" -d "$$POSTGRES_DB"' < "$(BACKUP_FILE)"
	@echo "Banco restaurado com sucesso a partir de $(BACKUP_FILE)"

