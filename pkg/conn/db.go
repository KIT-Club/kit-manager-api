package conn

import (
	"fmt"

	nsconfig "kit-manager-api/config"

	"gorm.io/driver/mysql"
	"gorm.io/gorm"
)

func NewMySQLConnection(dbCfg *nsconfig.DBConfig, err error) (*gorm.DB, error) {
	if err != nil {
		return nil, err
	}
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?charset=utf8mb4&parseTime=True&loc=Local", dbCfg.DbUsername, dbCfg.DbPassword, dbCfg.DbHost, dbCfg.DbPort, dbCfg.DbDatabase)
	return gorm.Open(mysql.Open(dsn), &gorm.Config{})
}
