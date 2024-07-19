up:
	docker compose up --force-recreate --remove-orphans --build --detach && make shell
log:
	docker compose up --force-recreate --remove-orphans --build
shell:
	docker compose exec php bash
ps:
	docker compose ps
down:
	docker compose down
