<?php
$pes = explode('/', $this->path);
array_pop($pes);
$this->curdir = array_pop($pes);

