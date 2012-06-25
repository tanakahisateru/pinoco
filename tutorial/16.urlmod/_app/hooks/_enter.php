<?php
function append_lang_param($url) {
    return $url . ((strpos($url, "?") === false) ? "?" : "&") . "lang=en";
}
$this->url_modifier = "append_lang_param";
