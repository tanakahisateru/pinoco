You already learned how to adjust your url to your site structure using `url:` modifier. If you want to convert link url with some riles globally, this is a chance to do it. You can set a callback to convert URL as you like.

Think about appending URL parameter "lang=en" to all URLs. You should do is only to set a callback to `url_modifier` of Pinoco. Your callback gets formatted URL, then you can change it to any form.

### _app/hooks/_enter.php
```php
<?php
function append_lang_param($url) {
    return $url . ((strpos($url, "?") === FALSE) ? "?" : "&") . "lang=en";
}
$this->url_modifier = "append_lang_param";
```

When "url:" modifier is like as:

```html
<p>Globally modified url: ${url:/foo/bar}.</p>
```

So, it would be:

```html
<p>Globally modified url: /16.urlmod/foo/bar?lang=en.</p>
```

For example, for Cookie-less browsers, it's useful to place session id into URL link to self site. (As you know, take care enough for security.)

Also you can change what should be done by checking original URL.