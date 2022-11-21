package nsgin

import (
	"context"
	"errors"
	"fmt"
	"net/http"
	"strings"

	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

type bindingType int64

const (
	BindType_JSON  bindingType = 1
	BindType_URI   bindingType = 2
	BindType_Query bindingType = 3
)

// Ordering: JSON, URI, Query
func BindData(c *gin.Context, req interface{}, bindingTypes ...bindingType) error {
	for _, v := range bindingTypes {
		switch v {
		case BindType_JSON:
			if err := c.BindJSON(req); err != nil {
				return err
			}
		case BindType_URI:
			if err := c.BindUri(req); err != nil {
				return err
			}
		case BindType_Query:
			if err := c.BindQuery(req); err != nil {
				return err
			}
		}
	}
	return nil
}

func GetCtx(c *gin.Context) *context.Context {
	if c != nil {
		ctx := c.Request.Context()
		return &ctx
	}
	return nil
}

func GetBearerToken(c *gin.Context) string {
	authorization, ok := c.Request.Header["Authorization"]
	if !ok {
		return ""
	}
	return strings.Replace(authorization[0], "Bearer ", "", 1)
}

// RESPONSE
func ResponseSuccess(c *gin.Context, data interface{}) {
	c.JSON(http.StatusOK, data)
}

func ResponseNoContent(c *gin.Context) {
	c.JSON(http.StatusNoContent, nil)
}

func ResponseCreated(c *gin.Context) {
	c.JSON(http.StatusCreated, nil)
}

func ResponseBadRequest(c *gin.Context, err error) {
	ResponseErrorWithCode(c, http.StatusBadRequest, err)
}

func ResponseInternalServerError(c *gin.Context, err error) {
	if IsNotFoundDBError(err) {
		ResponseNotFound(c, err)
		return
	}
	ResponseErrorWithCode(c, http.StatusInternalServerError, err)
}

func ResponseUnauthorized(c *gin.Context, err error) {
	ResponseErrorWithCode(c, http.StatusUnauthorized, err)
}

func ResponseNotFound(c *gin.Context, err error) {
	if err == nil {
		err = errors.New("not found")
	}
	ResponseErrorWithCode(c, http.StatusNotFound, err)
}

func ResponseErrorWithCode(c *gin.Context, code int, err error) {
	if err == nil {
		err = errors.New("")
	}
	c.JSON(code, map[string]string{
		"code":    fmt.Sprintf("%d", code),
		"message": err.Error(),
	})
}

func IsNotFoundDBError(err error) bool {
	if err == nil {
		return false
	}
	return errors.Is(err, gorm.ErrRecordNotFound)
}

// CORS
func CORSMiddleware(c *gin.Context) {
	c.Writer.Header().Set("Access-Control-Allow-Origin", "*")
	c.Writer.Header().Set("Access-Control-Allow-Methods", "*")
	c.Writer.Header().Set("Access-Control-Allow-Headers", "*")
	if c.Request.Method == "OPTIONS" {
		c.AbortWithStatus(200)
	} else {
		c.Next()
	}
}
