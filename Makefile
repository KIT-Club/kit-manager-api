include .env
export

.PHONY: build

dev:
	go run cmd/http/main.go

swag:
	swag init --parseDependency --parseInternal --parseDepth 1 --dir ./service/http --output doc

cmig:
	sql-migrate new $(name)

umig:
	sql-migrate up

dmig:
	sql-migrate down