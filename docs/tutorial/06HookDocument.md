In this tutorial, through an experiment about hook script, you can learn to write script to hook an access to web document.

Remember [[Tutorial01HelloWorld ]], we create `index.html.php` under `_app/hooks`.

```php
<?php
$this->message = "World";

```

This is **hook script** corresponding to `index.html`. The hook script is a process executed before showing corresponding HTML page. When HTML page would be shown, if the file named "public file name + .php" exists under `_app/hooks`, it should be executed as PHP script.

`_app/hooks/sub/index.html.php`  corresponds to `sub/index.html`. Create files below an access `sub/` with your browser:

### sub/index.html

```html
<p>${this/message}.</p>

```

### _app/hooks/sub/index.html.php

```php
<?php
$this->message = "This is a sub contents index.";

```

You can understand that a text in `$this->message` is shown in `<P>` tag.

Similarly, `sub/sibling.html`, `sub/child/index.html` and `sub/child/sibling.html` can be treat like above.

### sub/sibling.html

```html
<p>${this/message}.</p>

```

### _app/hooks/sub/sibling.html.php

```php
<?php
$this->message = "This is a sub contents sibling.";

```

### sub/child/index.html

```html
<p>${this/message}.</p>

```

### _app/hooks/sub/child/index.html.php

```php
<?php
$this->message = "This is a child contents index.";

```

### sub/child/sibling.html

```html
<p>${this/message}.</p>

```

### _app/hooks/sub/child/sibling.html.php

```php
<?php
$this->message = "This is a child contents sibling.";

```

Your pages are all hooked by your PHP script before rendering HTML.

*Hey, why do we need to create tow files purposely to show only one page? Isn't better to use plain PHP? We can't agree about your view logic isolation or such as.*

Well, yes, yes exactly. Although I can't stop your way if they are as is.

But please wait! The story still not finished. At the next section, you will learn about more hook script not only view/logic isolation.