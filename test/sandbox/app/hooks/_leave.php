<?php
// How to show status after page rendering (buffering must be enabled)
if(!preg_match('/\sFirePHP\//', $this->request->server->get('HTTP_USER_AGENT'))) {
    $this->skip(); // cancel this script here
}

@include_once('FirePHPCore/FirePHP.class.php');
if(class_exists('FirePHP')) {
    $firephp = FirePHP::getInstance(true);
    $firephp->group('Pinoco activity', array('Collapsed'=>1));
    foreach($this->activity as $act) {
        $firephp->log($act);
    }
    $firephp->log($this->script);
    $firephp->groupEnd();
}

