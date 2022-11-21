package service

import (
	"kit-manager-api/pkg/nsgin"

	"github.com/gin-gonic/gin"
)

// @title						KIT Manager API
// @securityDefinitions.apikey 	BearerAuth
// @in 							header
// @name 						Authorization
func Swag() {
}

func HelloWorld(c *gin.Context) {
	nsgin.ResponseSuccess(c, "Hello world!")
}
