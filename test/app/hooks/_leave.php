<?php
// How to show status after page rendering (buffering must be enabled)
if(!preg_match('/\sFirePHP\//', $_SERVER['HTTP_USER_AGENT'])) {
    $this->skip(); // cancel this script here
}
if($this->using('FirePHPCore/FirePHP.class.php')) {
    $firephp = FirePHP::getInstance(true);
    $firephp->group('Pinoco activity', array('Collapsed'=>1));
    foreach($this->activity as $act) {
        $firephp->log($act);
    }
    $firephp->log($this->script);
    $firephp->groupEnd();
}

