<?php
$post = $this->request->post;
if (trim($post->get('usertext', "")) == "") {
    $this->page = '_fail.html';
}
else {
    $this->page = '_success.html';
}
