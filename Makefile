start-wsl:
	sudo service docker start
export:
	$(shell echo "$$PATH")
up:
	docker-compose up -d
down:
	docker-compose down