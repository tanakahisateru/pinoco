<?php
$img = $this->sysdir . "/PinocoFlow.png";
$this->header("Content-Type:" . $this->mimeType($img));
if(!$this->testing) {
    readfile($img);
}

