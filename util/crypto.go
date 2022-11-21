package util

import b64 "encoding/base64"

func B64Encode(str string) string {
	return b64.StdEncoding.EncodeToString([]byte(str))
}
