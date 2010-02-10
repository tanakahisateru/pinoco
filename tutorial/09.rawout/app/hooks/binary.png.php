<?php
$img = $this->sysdir . "/PinocoFlow.png";
header("Content-Type:" . $this->mimeType($img));
readfile($img);

