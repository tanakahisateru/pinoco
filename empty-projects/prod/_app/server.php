<?php
$req = preg_split('/\?/', $_SERVER['REQUEST_URI']);
$path = array_shift($req);
$param = implode('?', $req);
$reqfile = $_SERVER['DOCUMENT_ROOT'] . $path;
$is_html = preg_match('/(\.html?|\.php)$/', $reqfile); // like as .htaccess
if($is_html || !is_file($reqfile)) {
    $_SERVER['PATH_INFO'] = $path;
    $_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'] . '/_gateway.php';
    $_SERVER['SCRIPT_NAME'] = '/_gateway.php';
    $_SERVER['PHP_SELF'] = '/_gateway.php' . $path;
    chdir($_SERVER['DOCUMENT_ROOT']);
    require $_SERVER['DOCUMENT_ROOT'] . '/_gateway.php';
    // To display log in CLI use stderr
    $stderr = fopen('php://stderr', 'w');
    fprintf($stderr, "[%s] %s:%d [???]: %s\n",
        @strftime('%c'), $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_PORT'],
        $_SERVER['REQUEST_URI']
    );
}
else {
    return false;
}
