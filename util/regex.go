package util

import "regexp"

func Regex(str string, pattern string) bool {
	match, _ := regexp.MatchString(pattern, str)
	return match
}
