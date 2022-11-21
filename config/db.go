package config

import "github.com/caarlos0/env/v6"

type DBConfig struct {
	DbHost     string `env:"DB_HOST"`
	DbPort     string `env:"DB_PORT"`
	DbUsername string `env:"DB_USERNAME"`
	DbPassword string `env:"DB_PASSWORD"`
	DbDatabase string `env:"DB_DATABASE"`
}

func GetDBConfig(err error) (*DBConfig, error) {
	if err != nil {
		return nil, err
	}
	cfg := DBConfig{}
	if err := env.Parse(&cfg); err != nil {
		return nil, err
	}
	return &cfg, nil
}
