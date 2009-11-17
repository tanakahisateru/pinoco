<?php
class TestController extends Pinoco_Delegate {
    
    function __construct() {
        $this->data = Pinoco::newvars(array(
            "item00" => "foo",
            "item01" => "bar",
        ));
    }
    
    function _empty() {
        $this->index();
    }
    
    function index() {
        // You can use "this" variable like as Pinoco instance here.
        $this->items = $this->data;
        $this->page = "_index.html";
    }
    
    function show() {
        $id = isset($_GET['id']) ? $_GET['id'] : "x";
        if($this->data->has($id)) {
            $this->item = $this->data[$id];
        }
        else {
            $this->item = "no item for " . $id;
        }
        $this->page = "_show.html";
    }
    
    function _default() {
        $this->notfound();
    }
}

