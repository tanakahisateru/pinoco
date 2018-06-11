<?php
if ($this->request->get->has('err')) {
    $this->error($this->request->get->get('err'));
} else {
    echo "No errors";
}
