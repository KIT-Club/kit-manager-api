package main

import (
	"kit-manager-api/config"
	"kit-manager-api/doc"
	"kit-manager-api/pkg/nsgin"

	httpService "kit-manager-api/service/http"

	"github.com/gin-gonic/gin"

	"github.com/joho/godotenv"
	swaggerFiles "github.com/swaggo/files"
	ginSwagger "github.com/swaggo/gin-swagger"
)

func initSwagger(r *gin.Engine, err error) error {
	swaggerCfg, err := config.GetSwaggerConfig(err)
	if err != nil {
		return err
	}

	doc.SwaggerInfo.Title = "KIT Manager API"
	doc.SwaggerInfo.Version = "1.0"
	doc.SwaggerInfo.Host = swaggerCfg.URL
	doc.SwaggerInfo.BasePath = swaggerCfg.Basepath
	doc.SwaggerInfo.Schemes = []string{swaggerCfg.Scheme}

	r.GET("/swagger/*any", ginSwagger.WrapHandler(swaggerFiles.Handler))

	return nil
}

func initRouter(err error) (*gin.Engine, error) {
	if err != nil {
		return nil, err
	}

	r := gin.Default()
	r.Use(nsgin.CORSMiddleware)

	if initSwagger(r, nil) != nil {
		return nil, err
	}

	r.GET("/", httpService.HelloWorld)

	return r, nil
}

func main() {
	godotenv.Load()
	router, err := initRouter(nil)
	if err != nil {
		panic(err)
	}

	// run server
	if err := godotenv.Load(".env"); err != nil {
		panic(err)
	}
	router.Run()
}
