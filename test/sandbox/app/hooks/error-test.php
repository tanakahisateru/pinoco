<?php
if(isset($_GET['err'])){
    $this->error($_GET['err']);
}
else {
    echo "No errors";
}
