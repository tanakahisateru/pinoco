<?php
/* @var $this Pinoco */

$self = $this;
$data = array(
    'data' => array(
        array('id' => 1, 'name' => 'foo'),
        array('id' => 2, 'name' => 'bar'),
    ),
);

$this->router()->pass(array('', 'index.html',))
    ->on('list', function () use ($self, $data) {
        $self->header('Content-Type:application/json');
        $self->page = null;
        echo json_encode($data);
        $self->terminate();
    })
    ->on('show/{id}', function ($id) use ($self, $data) {
        foreach ($data['data'] as $item) {
            if ($item['id'] == $id) {
                $self->header('Content-Type:application/json');
                $self->page = null;
                echo json_encode($item);
                return;
            }
        }
        $self->notfound();
    })
    ->on('POST:post', function () use ($self, $data) {
        // transaction
        $self->header('Content-Type:application/json');
        $self->page = null;
        echo json_encode(array('code' => 'OK'));
        $self->terminate();
    })
    ->on('GET:post', array($this, 'forbidden'))
    ->on('*', array($this, 'notfound'))
;
