Pinoco has a bit nifty feature which called "Auto local variable".

I regular case when you want to share some context between each scripts, you should use `this` of Pinoco instance as web request context. In this way, to reference variable, typing `$this` or `this/` every time is annoying work. You will assign often used variables to another variable in script local scope.

```php
$db_connection = $this->db_connection;
$db_connection->...;
$db_connection->...;

```

Auto-local variable reduce the time to assign a property of `this` to another local variable. It's easy to use: only substitute a value to property of `$this->autolocal` collection. Do once so, continuing script and page starts with already existing these variables.

### _app/hooks/_enter.php

```php
<?php
$this->autolocal->context = "World";

```

### _app/hooks/index.html.php

```php
<?php
// Autolocal variables are automatically expanded to local scope.
// Equival code:  $context = $this->autolocal->context;

$this->autolocal->message = $context;
// Auto local variable is passed to view.

```

### index.html

``` html
<p>Hello ${message}.</p>

```

`context`(defined in `_app/hooks/_enter.php`) is used in `_app/hooks/index.html.php` like as local variable. And `message`(defined in `_app/hooks/index.html.php`) is used in HTML.page.

Note: in the same script where you assign to `$this->autolocal` only, the local variable is still not available. If you want to use it immediately, you should give some name explicitly. Automatically expansion only works at the begging of each scripts.
