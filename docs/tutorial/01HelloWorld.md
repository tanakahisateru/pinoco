Lets create the simplest site using Pinoco. It will show "Hello World" on web page. Don't you know about this phrase? Well, it means like a benchmark test about code writing or such as.

Create a HTML document `index.html` and edit as following:

```html
<p>Hello ${this/message}.</p>

```

This is the smallest PHPTAL syntax having a variable. Have you ever seen? OK, instead, you can change it as:

```html
<p>Hello <span tal:replace="this/message">Someone</span>.</p>

```

Dollar character and brace are gone out from text node. PHPTAL's syntax is pure XHTML only namespace extended. Using this format, annoying magical characters are never shown when you drop your HTML over your browser from local file system.

Although it's too loose HTML, you can wrap this example with HTML tag and BODY tag. However, be sure to hold it as XML. Though needless to follow all XHTML specs but at least to be valid XML is necessary. Are you sure to close tag strictly? Did you insert a slash to the end of single tag?

Look into the variable. Usually in Pinoco app, `this` points a instance of Pinoco class (except inside of user original class and function). Pinoco instance act as contextual state holder between request and response.

Click your browser's reload button, you will see quit noisy error. PHPTAL checks existence of variable strictly. The error message tell you that Pinoco doesn't have an "message" variable.

To let quite PHPTAL, fill "message" variable of Pinoco. Create `_app/hooks/index.html.php` and write PHP script as:

```php
<?php
$this->message = "World";

```

You can set any property like *foo*, *bar* or such as to Pinoco instance.

Closing PHP tag is unnecessary if it has no HTML block. Rather, in case of logic only scripting, it's safer way than closing not to be rendered trailing CR/LF as HTML.

You've just created two files (1 or 2 lines). Now, when click your browser's reload button "Hello World" would be shown. Confirm the result of HTML source code using your browser.

```html
<p>Hello World.</p>

```

Congratulations! Please say hello to world, space or everything. If you are tired to greet, let's go to the next tutorial.