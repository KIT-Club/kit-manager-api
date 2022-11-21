package model

import (
	"errors"
	"fmt"
	"kit-manager-api/message"
)

var (
	ErrMethodNotAllowed    = errors.New(message.ErrMethodNotAllowed)
	ErrInternalServerError = errors.New(message.ErrInternalServerError)
	ErrUnauthorized        = errors.New(message.ErrUnauthorized)
	ErrInvalidToken        = errors.New(message.ErrInvalidToken)
)

type ErrorExtra struct {
	Error  error
	Code   int
	Prefix string
}

func (ee *ErrorExtra) Print() {
	fmt.Println(ee.Prefix + ": " + ee.Error.Error())
}
