package config

import "github.com/caarlos0/env/v6"

type SystemConfig struct {
	Mode string `env:"MODE"`
}

func GetSystemConfig(err error) (*SystemConfig, error) {
	if err != nil {
		return nil, err
	}
	cfg := SystemConfig{}
	if err := env.Parse(&cfg); err != nil {
		return nil, err
	}
	return &cfg, nil
}
