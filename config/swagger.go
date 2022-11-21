package config

import "github.com/caarlos0/env/v6"

type SwaggerConfig struct {
	URL      string `env:"URL"`
	Basepath string `env:"BASEPATH"`
	Scheme   string `env:"SCHEME"`
}

func GetSwaggerConfig(err error) (*SwaggerConfig, error) {
	if err != nil {
		return nil, err
	}
	cfg := SwaggerConfig{}
	if err := env.Parse(&cfg); err != nil {
		return nil, err
	}
	return &cfg, nil
}
