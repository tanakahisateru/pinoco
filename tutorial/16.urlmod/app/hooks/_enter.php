<?php
function append_lang_param($url) {
    return $url . ((strpos($url, "?") === FALSE) ? "?" : "&") . "lang=en";
}
$this->url_modifier = "append_lang_param";
