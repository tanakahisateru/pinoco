<?php
// You can implement a security filter easily.
if (!$this->authorized) { // This condition would be failed always.
    $this->forbidden();
}
