When we want to share design assets commonly on creating a website, we would get the problem "URL reference to other pages or CSS link are different each other between pages in different hierarchy". Learn how to adjust your URL dynamically using Pinoco.

Pinoco has a feature which fixes URL in situation.

Create you  project as `04.resolveurl` and put 3 documents:

* index.html
* sub/index.html
* sub/sibling.html 

Make link each with all another:

### index.html

```html
<tal:block>
<h1>Site Root /</h1>
<p><a href="sub/index.html">Link to sub/</a></p>
<p><a href="sub/sibling.html">Link to sub/sibling.html</a></p>
</tal:block>
```

### sub/index.html

```html
<tal:block>
<h1>Sub /sub/index.html</h1>
<p><a href="../index.html">Link to site root</a></p>
<p><a href="sibling.html">Link to sibling.html</a></p>
</tal:block>
```

### sub/sibling.html

```html
<tal:block>
<h1>Sibling of sub /sub/sibling.html</h1>
<p><a href="../index.html">Link to site root</a></p>
<p><a href="index.html">Link to sub/index.html</a></p>
</tal:block>
```

As you know, they are not the same description to point the same contents in different hierarchy. If they are included as shared library, you should do something much. If so, you might specify base URLs every time.

Should we let URL be absolute?

No, though an absolute URL let the same description be the same semantic, but you can't preview them under local file system while authoring. And more, your website can't be portable to replace to another hierarchy or other host. (e.g. From sub-path of internal test server to root of production server.)

Resolve all of these problems with Pinoco.

Rewrite `sub/index.html` as:

```html
<tal:block>
<h1>Sub /sub/index.html</h1>
<p><a href="../index.html" tal:attributes="href url:/">Link to site root</a></p>
<p><a href="sibling.html">Link to sibling.html</a></p>
</tal:block>
```

I added a new attribute `tal:attributes="href url:/"` to the first tag. Then, this href attribute will be replaced with **the path which points your private website's root path**. String trailing with `url:` is "the path from root of your contents".

Look into by your web browser. 
If you place your website under `http://localhost/04.resolveurl/`, it would be:
```html
<p><a href="/04.resolveurl/">Link to site root</a></p>
```

In the other hand, under `http://localhost/foo/bar/` it would be:
```html
<p><a href="/foo/bar/">Link to site root</a></p>
```

Now, you can split a part of `sub/index.html` to `_taglib.html` (directly placed under `htdocs`).

### _taglib.html

```html
<tal:block>
<p metal:define-macro="to_site_root">
<a href="index.html" tal:attributes="href url:/">Link to site root</a>
</p>
</tal:block>
```

### sub/index.html

```html
<tal:block>
<h1>Sub /sub/index.html</h1>
<p metal:use-macro="_taglib.html/to_site_root">Link to site root</p>
<p><a href="sibling.html">Link to sibling.html</a></p>
</tal:block>
```

Successfully we can let a hyperlink be macro.

Because the `htdocs` of Pinoco is in a search path PHPTAL, every page can refer it by only writing `_taglib.html`. Of course you can point this macro with relative path from current file like `../_taglib.html`.

Change others to use it.

### sub/sibling.html

```html
<tal:block>
<h1>Sibling of sub /sub/sibling.html</h1>
<p metal:use-macro="_taglib.html/to_site_root">Link to site root</p>
<p><a href="index.html">Link to sub/index.html</a></p>
</tal:block>
```

### index.html

```html
<tal:block>
<h1>Site Root /</h1>
<p metal:use-macro="_taglib.html/to_site_root">Link to site root</p>
<p><a href="sub/index.html">Link to sub/</a></p>
<p><a href="sub/sibling.html">Link to sub/sibling.html</a></p>
</tal:block>
```

Result of `index.html` that is placed at different hierarchy from `sub/index.html` is:

```html
<h1>Site Root /</h1>
<p><a href="/04.resolveurl/">Link to site root</a></p>
```

The same description can be correct path even if they are different base path.

Link to `sub/index.html` is to be `url:/sub/`, and to `sub/sibling.html` is `url:/sub/sibling.html`. You can just rewrite all of hyperlinks in this 3 pages and test on web server how they are.

# In Advance

URL modifier `url:` supports not only absolute path from website root but also relative path from current base path. And more, it retains URL parameters. And you can use this modifier also outside `tal:attibutes`.

```html
<tal:block>
<h1>Site Root /</h1>
<p><a href="sub/index.html" tal:attributes="href url:/sub/">Link to sub/</a></p>
<p><a href="sub/index.html" tal:attributes="href url:sub/">Link to sub/</a></p>
<p><a href="sub/index.html" tal:attributes="href url:/sub/index.html?foo=1&bar=2">Link to sub/index.html with parameres</a></p>
<p>URL for sub contents is ${url:/sub/}.</p>
</tal:block>
```

If the target path is in variable, you can use PHPTAL's variable expression (know as tales). Whole of text below `url:` is treated as `sting:` part in PHPTAL.

```php
<?php
$this->path_to_sub = "/sub/";
```

```html
<p><a href="sub/index.html" tal:attributes="href url:${this/relpath_to_sub}">Link to sub/</a></p>
```

URL resolution can be completed in hook script. Pinoco has `url()` method to do the same effect as `url:` modifier. It's needless to decorate so much.

```php
<?php
$this->url_for_sub = $this->url("/sub/");
```

```html
<p><a href="sub/index.html" tal:attributes="href this/url_for_sub">Link to sub/</a></p>
```
