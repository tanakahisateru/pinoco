## Q. How to write JavaScript that contains HTML tag?

OK, I know JavaScript code is often written in HTML file, and they contains `<div>` or such as. If you write them straightly in casual way, it crashes SAX parser of PHPTAL.

I prefer to use CDATA section:

```html
<script type="text/javascript">
<![CDATA[
document.write('<div>${structure this/data/plain_text_in_js}</div>');
// You can write HTML tag in your script safer.
]]>
</script>
```

Then, use PHPTAL's output mode `PHPTAL::HTML5` in `_enter.php` if you don't like CDATA.

```php
require_once('PHPTAL.php');
$this->renderers->html->cfg->outputMode = PHPTAL::HTML5;
```

will be rendered as:

```html
<script type="text/javascript">

document.write('<div>...</div>');
// You can write HTML tag in your script safer.

</script>
```

Script comment looks working fine at first, but it can't expand any dynamic values.

```html
<script type="text/javascript">
<!--
document.write('<div>${structure this/data/plain_text_in_js}</div>');
// BAD EXAMPLE: ${...} is shown as is.
//-->
</script>
```

...will show a variable name as is because it is pure XML comment node(PHPTAL never parse XML comment).

## Q. What is the best practice about multiline text?

TAL escapes unsafe text automatically. Failure way:

```html
<p tal:content="ntext"></p>
<p tal:content="php:nl2br(ntext)"></p>
```

will be converted as:

```html
<p>Hello
Pinoco&amp;PHPTAL</p>
<p>Hello&lt;br /&gt;Pinoco&amp;PHPTAL</p>
```

The answer for pure PHPTAL is this.

```html
<p tal:content="structure php:nl2br(htmlspecialchars(ntext))"></p>
```

PHPTAL namespace extension PAL has been shipped in Pinoco 0.5.

```html
<p pal:content-nl2br="ntext"></p>
```

Both give you HTML you expected.

```html
<p>Hello<br />
Pinoco&amp;PHPTAL</p>
```

PAL namespace has also `pal:replace-nl2br` for the same purpose.

## Q. When should href be replaced by url modifier?

In most of standard pages displayed in your address bar, you don't need URL rewriting. Leave them as relative URL.

But in reused paged which has TAL macro, you must replace href and src attributes to absolute URI. Because they will be used by pages in different base URI.

```html
<header metal:define-macro="header">
  <nav id="main-menu"><ul>
    <li><a href="index.html" tal:attributes="href url:/">HOME</a></li>
    <li><a href="contact/index.html" tal:attributes="href url:/contact/index.html">Contact</a></li>
    <li><a href="about/index.html" tal:attributes="href url:/about/index.html">About us</a></li>
  </ul></nav>
</header>
```

Pinoco has shorter way to do it since 0.5.

```html
<header metal:define-macro="header">
  <nav id="main-menu"><ul>
    <li><a href="index.html"         pal:attr="href url:/">HOME</a></li>
    <li><a href="contact/index.html" pal:attr="href url:/${attr/href}">Contact</a></li>
    <li><a href="about/index.html"   pal:attr="href url:/${attr/href}">About us</a></li>
  </ul></nav>
</header>
```
