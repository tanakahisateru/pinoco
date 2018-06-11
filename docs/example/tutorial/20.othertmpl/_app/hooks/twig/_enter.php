<?php
require_once "TwigRenderer.php";

$this->page_modifier = null; // reset

function page_ext_html2twig($path)
{
	if (preg_match('/\/$/', $path)) {
		return $path . 'index.html.twig';
	} else {
		return preg_replace('/(.*)\.html$/', '${1}.html.twig', $path);
	}
}
$this->page_modifier = 'page_ext_html2twig';

$this->renderers->twig = new TwigRenderer($this);
