<?php
if(trim($_POST['usertext']) == "") {
    $this->page = '_fail.html';
}
else {
    $this->page = '_success.html';
}
