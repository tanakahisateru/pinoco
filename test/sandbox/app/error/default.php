<?php
header("HTTP/1.0 " . $code . " " . $title);
header("Content-Type: text/html; charset=iso-8859-1");
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
<head>
<title><?php echo $code . " " . $title; ?></title>
</head>
<body>
<h1><?php echo $code . " " . $title; ?></h1>
<p><?php echo $message; ?></p>

<hr>
<pre><?php
var_dump($this);
?></pre>

</body>
</html>
