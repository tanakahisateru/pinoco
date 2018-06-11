In this section, you can learn how to change settings of PHPTAL in Pinoco.

In "Configuration methods" section of PHPTAL's manual, there are many options.

Pinoco's page renderer for PHPTAL is stored in `$this->renderers->html`. This `html` property stores a responder object for web requests with `*.html` suffix. The default object is of course PHPTAL renderer. (Raw PHP renderer is stored in `$this->renderers->php`)

You can set some properties of this renderer object. PHPTAL renderer has `cfg` property to pass custom settings to PHPTAL. User properties are mapped to corresponding methods of PHPTAL.

See example:

<table>
<tr><td>setOutputMode</td><td>html->cfg->outputMode</td></tr>
<tr><td>setForceReparse</td><td>html->cfg->forceReparse</td></tr>
</table>

### _app/hooks/_enter.php

```php
<?php
require_once "PHPTAL.php";
$this->renderers->html->cfg->outputMode = PHPTAL::HTML5;
$this->renderers->html->cfg->forceReparse = TRUE;
```
`setOutputMode()` and `setForceReparse()` would be called when HTML content is generated. 
(To use `PHPTAL::HTML5` constant I load `PHPTAL.php` earlier than it.)

OK, check if it alive.

### index.html

```html
<p>Hello World.<br/>
Look into HTML source. How BR tag is displayed?
</p>
```

This HTML file is written in strict XHTML. But so I configure `outputMode` to HTML5. Result: 

```html
<p>Hello World.<br>
Look into HTML source. How BR tag is displayed?
</p>
```

Look into `br` tag, you can see the trailing slash was lost. We can change output mode to HTML5 compatible style!

You've just learned HOW TO change that, and you will know WHAT via PHPTAL's manual when you need it later.