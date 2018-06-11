Pinoco is possible to coexist with other frameworks: general methods to use two or more frameworks.

It's easy to dispatch to other framework is earlier rewriting before arrival to `_gateway.php`.

### .htaccess
```apacheconf
RewriteRule ^(other/.*)$   $1 [L,QSA]
```

Or instead, you might like using RewiteCond.

### .htaccess
```apacheconf
RewriteCond %{REQUEST_URI} !^/your_site_uri/other/.*$
RewriteRule ^(.*)$   _gateway.php/$1 [L]
```

`other` directory is free from Pinoco's control. You can put anything (even a big application) in this directory.

Your `.htaccess` (and also `_gateway.php`) is free from license of Pinoco.

