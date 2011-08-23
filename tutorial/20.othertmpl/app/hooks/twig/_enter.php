<?php
$this->page_modifier = null; // reset

require_once "TwigRenderer.php";
$this->renderers->html = new TwigRenderer($this);
