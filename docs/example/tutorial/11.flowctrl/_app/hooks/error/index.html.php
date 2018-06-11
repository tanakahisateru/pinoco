<?php
if (1) {
    $this->error(500, "Internal server error");
    // $this->forbidden() is alias to $this->error(403, ...)
    // $this->notfound() is alias to $this->error(404, ...)
}

echo "This line would not be executed.";
