<?php
class TestController {
    
    function __construct() {
        $this->data = Pinoco::newVars(array(
            "item00" => "foo",
            "item01" => "bar",
        ));
    }
    
    function index($pinoco) {
        // You can use "this" variable like as Pinoco instance here.
        $pinoco->items = $this->data;
        $pinoco->page = "_index.html";
    }
    
    function show($pinoco) {
        $id = isset($_GET['id']) ? $_GET['id'] : "x";
        if($this->data->has($id)) {
            $pinoco->item = $this->data[$id];
        }
        else {
            $pinoco->item = "no item for " . $id;
        }
        $pinoco->page = "_show.html";
    }
}

