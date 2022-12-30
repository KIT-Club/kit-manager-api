start-docker:
	sudo service docker start
up:
	docker-compose up -d
down:
	docker-compose down
kill:
	fuser -k 8000/tcp
dev:
	php artisan serve
swag:
	php artisan l5-swagger:generate