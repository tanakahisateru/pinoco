Configure Nginx to pass some requests (e.g. for non-existence or html/php) to _gateway.php.
And let _gateway.php to dispatch toward FastCGI with adding PATH_INFO like Apache's mod_php.
```
location / {
    root your/htdocs;
    if (!-f $request_filename) {
        rewrite ^/(.*)$ /_gateway.php last;
    }
    rewrite ^/(.*\.(html|php))$  /_gateway.php last;
}

location ~ _gateway\.php$ {
    root your/htdocs;
    fastcgi_pass   127.0.0.1:9000;
    fastcgi_index  _gateway.php;
    fastcgi_param  SCRIPT_FILENAME your/htdocs/$fastcgi_script_name;
    fastcgi_param  PATH_INFO       $request_uri;
    include        fastcgi.conf;
}
```

Serve PHP as FastCGI.
```
php-cgi -b 127.0.0.1:9000
```

Then put _gateway.php. It looks very clean!
```php
<?php
require_once 'Pinoco.php';
Pinoco::create("to/app/path", array())->run();
```

Don't forget to remove .htaccess from your document root.
Enjoy!