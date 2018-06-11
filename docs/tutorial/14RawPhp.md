We Pinoco project prefer to use PHPTAL as template engine. But sometimes you don't want to use extra template engine than PHP. Pinoco allows to use raw PHP as view template instead of PHPTAL.

Try to create an raw PHP version of "Hello world" app.

### index.php
```php
<p>Hello <?php echo htmlspecialchars($this->message); ?>.</p>
```

### _app/hooks/index.php.php
```php
<?php
$this->message = "World";
```

It finished only that! It's not so far from Tutorial01HelloWorld, only extension of view file changed to ".php".

Note that this PHP script is executed not in global scope. If you want to use some global variable, you must declare those with global keywords. In this view implement way, regular variables used in view are locally closed, so you can be free from confliction of global variables. And you can see `$this` variable pointing Pinoco instance. Totally it's similar to hook scripts.

Is this good for beginners to avoid to learn PHPTAL? No, if without PHPTAL, you remember using `htmlspecialchars` to show something every time and you should call `$this->url(...)` explicitly to fix URL. To use PHPTAL let you free from those annoying issues, and you can get complete XHTML compatibility and high level macro features.

It's better to choose raw PHP view for only performance or non-regular formed HTML. We prefer PHPTAL with Pinoco, again.