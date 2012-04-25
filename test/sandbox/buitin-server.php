<?php
/**
 * php -S localhost:8081 -t path/to/docroot/ buitin-server.php
 */
$req = preg_split('/\?/', $_SERVER['REQUEST_URI']);
$path = array_shift($req);
$param = implode('?', $req);
$reqfile = $_SERVER['DOCUMENT_ROOT'] . $path;
if(preg_match('/(\.html?|\.php)$/', $reqfile) || !is_file($reqfile)) {
    $_SERVER['PATH_INFO'] = $path;
    $_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'] . '/_gateway.php';
    $_SERVER['SCRIPT_NAME'] = '/_gateway.php';
    $_SERVER['PHP_SELF'] = '/_gateway.php' . $path;
    chdir($_SERVER['DOCUMENT_ROOT']);
    require $_SERVER['DOCUMENT_ROOT'] . '/_gateway.php';
}
else {
    return false;
}
