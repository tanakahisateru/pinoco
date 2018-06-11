Here, you can learn how to change view according to result of action.

When user may post absolutely invalid data, you can't display the right page. We try to create a form app that can branch to error scenario.

### index.html

```html
<form action="check" method="post">
    <input name="usertext" type="text" value="" />
    <input type="submit" />
</form>

```

User posts a value named as `usertext` to URI of `check`. Show success page when it is not empty but when the value is empty,  change it to failure page.

Two tasks we should do.

1. Check the value from the form.
2. Determine which HTML should be shown.

Post target URL "check" is  now virtual resource. If you create a file named as "check" but Pinoco can't render it because Pinoco uses file name extension to determine template engine. So you should explicitly set a view to render result.
  
### _app/hooks/check.php

```php
<?php
if(trim($_POST['usertext']) == "") {
    $this->page = '_fail.html';
}
else {
    $this->page = '_success.html';
}

```

`_fail.html` or `_success.html` are set to "page" variable of Pinoco instance. Pinoco would not find out the default view file and use the file you set.

Prepare both of `_fail.html` and `_success.html` before check above logic.

### _fail.html

```html
<p>Fail. Please input some text.</p>

```

### _success.html

```php
<p>Success!</p>

```

The file name starts with underscore. To do so, the view file would be private resource and anyone can't access it directly. To hide unexpected file access makes your app more secure.

Now, try to post something from the form, and check to post nothing. You will see that the same URL shows absolutely different content. This is a basic way to switch application to another flow.

Explicit "page" specifying is useful even if some default HTML is presented. For example, when unauthorized user may try to access contents for members only you can show login form with overriding "page" property. You will create various applications with this easy way.
