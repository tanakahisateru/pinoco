In this tutorial, you can learn about non-HTML output. Dynamic contents is not only HTML. Sometimes you should send none markup text like JSON, binary files or images.

### index.html

```html
<tal:block>
<p>Click to show <a href="plain.txt">plain text content</a>.</p>
<p>Click to show <a href="binary.png">binary data content</a>.</p>
</tal:block>
```

Let's output `plain.txt` and `binary.png` dynamically.

Pinoco's standard mod_rewrite configured as "Skip existing non-HTML files". PHP would never used for static resource. This setting gives you totally better performance. So, NEVER place `plain.txt` and `binary.png` in your site. 

How should we do?

I tell you a technic of "writing hook script without real document". Pinoco checks your document hook script when required static content is not found in web public folder. If you have an hook script matched for URI (file name matching or _default but else _enter), "404 not found" error would be canceled and correct empty output would be given. You can fill any contents into it manually.
 
### _app/hooks/plain.txt.php

```php
<?php
$this->header("Content-Type:text/plain");
echo "This is plain text content.";
```

Plain text will be displayed in your browser. This script looks like Prel CGI, but it's URL(`/plain.txt`) looks pure plain text file. Web visitors can save it as named file as is.

Binary output is implemented as the same way:

### _app/hooks/binary.png.php

```php
<?php
$img = $this->sysdir . "/PinocoFlow.png";
$this->header("Content-Type:" . $this->mimeType($img));
readfile($img);
```
It's easy. Now you can send binary stream.

`$this->mimeType()` is a utility method to get MIME-Type of file. This is simple wrapper because there are many methods for MIME-Type under PHP5. Well, you can write "image/png" here, instead.


Again, remember two standard rules: 

* Pinoco handles requests for "HTML files".
* Pinoco handles requests for "non-exisiting files".

In addition, I prefer to keep  below in you mind.

Request for non-existing file let PHP load Pinoco class and start scanning hooks directory tree. If that URL is simply nothing, it's to be needless overhead to show "404 not found". For example, if many image tags for non-existing files are in HTML, it will be stress of server by waste PHP invoking. At that time, you can customize `.htaccess` to skip Pinoco in pure static contents including folder, or skip "non-HTML file handling" in your condition.
