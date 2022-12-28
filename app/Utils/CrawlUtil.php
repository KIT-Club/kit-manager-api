<?php

namespace App\Utils;

class CrawlUtil
{
    public function getFieldFromHtml(String $html, String $field)
    {
        if (!preg_match("/id=\"" . $field . "\" value=\"(.+?)\"/", $html, $result))
            return "";
        return $result[1];
    }
}
