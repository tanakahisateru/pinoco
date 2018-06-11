In Pinoco's hook scripts you can add extra directories for external files to file search path of PHP's default setting (by php.ini).

`_app/lib` is registered as the default to it. Try to put some file in `lib` directory.

### _app/lib/myutil.php

```php
<?php
function foo() {
    return "foo";
}

```

You can read this file in regular way of PHP.

### _app/hooks/_enter.php

```php
<?php
require_once "myutil.php";

```

After this, you can use `$foo` as global variable. Of course you can use your PHP's default path concurrently. Base directory which `_gateway.php` is placed at and PEAR library path if you use are all visible for you. In addition, the directory which placed current hook script are added to the path automatically.

More directories can be additional search path as you like. When you use different series of libraries you can separate them each other. They might come from different source code repositories.

```php
$this->incdir->push($this->sysdir . "/altlib");


```

Then, you can add `altlib` under application directory as search path. `incdir` property is a PinocoList, so you can costruct it freely using any methods of PinocoList also else `push`.

The search path is configured just before beggining of each hook scripts. So, in regular case, you will set them in top-level `_enter.php` and use them under below. But when you want to use them just after configuration, you should update it explicitly.

```php
$this->incdir->push($this->sysdir . "/altlib");
$this->updateIncdir();

```

Now, check if external library can be used.

### _app/altlib/world_message.php

```php
<?php
function world() {
    return "World";
}

```

### _app/hooks/index.html.php

```php
<?php
require_once "world_message.php";
$this->message = world();

```

### index.html

```html
<p>Hello ${this/message}.</p>

```

Can you see "Hello World" in your browser?

Note: It's useful that the current hook script existing path is always added to seaech path. You can split your application logic to two or more files easily. For example `_app/hooks/foo/_utility.php` is visible to `_app/hooks/foo/action.php`. I prefer that you let your sub module begin with underscore e.g. `_utility.php` for security.