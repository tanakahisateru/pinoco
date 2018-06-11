In this section, you can learn how to control script execution chain.

If you want to stop script chain, use "skip" or "terminate".

For example, when you restrict data for unauthorized users, you might control it like as:

```php
if($authed) {
   // full contents shown
}
else {
   // blocked
}

```

Oops, on failure case, don't you want exit from this process earlier? Pinoco has "skip" method instead of "return" syntax.

```php
if(!$authed) {
  // blocked
  $this->skip();
}
// full contents shown

```

When skip would be called, this script aborted immediately and lines below "full contents shown" are ignored. And if any script to be executed you have, it goes to the next chained script.

Try it. Do skip in `_enter.php` and check how `index.html.php` would be.

### _app/hooks/skip/_enter.php

```php
<?php
$this->testvar1 = "initial value";
$this->testvar2 = "initial value";

if(1) {
    $this->skip();  // It works like return syntax.
}

$this->testvar1 = "changed in _enter";

```

### _app/hooks/skip/index.html.php

```php
<?php
$this->testvar2 = "changed in doc-hook";

```

### skip/index.html

```html
<ul>
    <li>testvar1 = ${this/testvar1}</li>
    <li>testvar2 = ${this/testvar2}</li>
</ul>

```

Try to visit "skip/index.html" to get a response as below:

```html
<ul>
    <li>testvar1 = initial value</li>
    <li>testvar2 = changed in doc-hook</li>
</ul>

```

Substitution for `testvar1` in `_enter.php` was skipped, and then substitution for `testvars2` in `index.html.php` was done. The `skip` method seems like error return at failure case.

There are another method named "terminate" similar to "skip". But it cancels all process of hook scripts planned later.

### _app/hooks/term/_enter.php

```php
<?php
$this->testvar1 = "initial value";
$this->testvar2 = "initial value";

if(1) {
    $this->terminate();  // Go renderign phase immediately.
}

$this->testvar1 = "changed in _enter";

```

### _app/hooks/term/index.html.php

```php
<?php
$this->testvar2 = "changed in doc-hook";

```

### term/index.html

```html
<ul>
    <li>testvar1 = ${this/testvar1}</li>
    <li>testvar2 = ${this/testvar2}</li>
</ul>

```

Differ from above example, `terminate` is called in `_enter.php`. Then it gives output below:

```html
<ul>
    <li>testvar1 = initial value</li>
    <li>testvar2 = initial value</li>
</ul>

```

Exactly `index.html.php` are ignored before rendering.

`terminate` is used when you want to block deeper access into your contents/application but without HTTP error.

Because It's designed for blocking deeply access, if your hook scripts chain would be aborted, each `_leave.php`s corresponds to already executed `_enter.php`s would be executed after rendering. Don't worry about ignoring your resource cleanup process in shallower structure.

As the last topic, there are other flow control API similer to `skip` and `terminate`. It is HTTP error invocation.

### _app/hooks/error/index.html.php

```php
<?php
if(1) {
    $this->error(500, "Internal server error");
    // $this->forbidden() is alias to $this->error(403, ...)
    // $this->notfound() is alias to $this->error(404, ...)
}

echo "This line would not be executed.";

```

`error` method raises HTTP error response. When it would be called, remaining all steps including page rendering are canceled and it returns HTTP error code back to your browser.

`forbidden` and `notfound` are shorted of `error` , so they behave as same. And `redirect` is also the same type method.

<table>
  <tr><th></th><th>Continues below</th><th>Goes to the next</th><th>Renders page</th></tr>
  <tr><td>skip</td><td>No</td><td>Yes</td><td>yes</td></tr>
  <tr><td>terminate</td><td>No</td><td>No</td><td>Yes</td></tr>
  <tr><td>error</td><td>No</td><td>No</td><td>No</td></tr>
</table>

To tell the truth, these control method are realized with escaping using exceptions. Note that you must not use them in try-catch block which takes (top-level) Exception class. Use more detailed exception always or check not to including these flow control method in loose try-catch carefully.