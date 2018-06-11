In this section, you can learn about handling default cases. Dynamic website should be able to provide multiple contents via single mechanism without adding any source code.

Think about developing a small SNS having blog for each users. We let users have individual blog, and more, optional services are planned to be enabled in future.

According above condition, we try to design URL. Of course, you can realize using URL parameters like as `/action_name.php?user=...&another_param=...`, but its a bit ugly. Forget CGI and think more rational URL.

<table>
<tr><td>/users/[user name]/index.html</td><td>Home of the user</td></tr>
<tr><td>/users/[user name]/blog/index.html</td><td>Blog home of the user</td></tr>
<tr><td>/users/[user name]/blog/[entry id].html</td><td>Each blog entries</td></tr>
<tr><td>/users/[user name]/.../...</td><td>Others for the user</td></tr>
<tr><td>/...</td><td>Others served globally</td></tr>
</table>

You can find two dynamic elements: "user name" and "entry id". How can we define these flexible handler?

Pinoco's hook/view mechanism allows you to use `_default` directory and `_default.*.php` script / `_default.html` page to accept any path element. If directory name would not match to URL element, it tries to go to `_default` directory instead of real name. At the end of path, `_default[.ext if presented].php` script or `_default.html` page may used instead.

Mr.Foo and Mr.Bar are members of our service. List them first.

### index.html

```html
<tal:block>
<p><a href="users/foo/">Mr.Foo</a></p>
<p><a href="users/bar/">Mr.Bar</a></p>
</tal:block>

```

# Home

Never create `foo` or `bar` under `users` directory, instead, create `_default` directory and `index.html`.

### users/*_*default/index.html

```html
<tal:block>
<p>I am ${this/user_name}.</p>
<p>See <a href="blog/">my blog</a>.</p>
</tal:block>

```

Home page for both of Mr.Foo(/users/foo/) and Mr.Bar(/users/bar/) use this HTML. Here, `$this->user_name` has still not be given.

Complete `$this->user_name` with path element in hook script. Because the name of user looks useful for below contents, in this case, it's good idea to use `_enter.php`.

### _app/hooks/users/*_*default/*_*enter.php

```php
 <?php
 $this->user_name = $this->pathargs[0]; // The 1st _default matching element.

```

The property `pathargs` of Pinoco is a list of path elements which matches `_default` directory or `_default.html` file. In this case, `foo` or `bar` are expected as the 1st element of this list.

Show `/users/foo/`, `/users/bar/` and `/users/any_random_name/` in your browser. You can confirm that a single mechanism generates two or more pages.


# Blog

Next, create user blog's home page which has top list of entries. Temporally you create as static prototype.

### users/*_*default/blog/index.html

```html
<tal:block>
<h1>${this/user_name} Blog</h1>
<p><a href="20091225.html">2009-12-25 Merry X'mas</a></p>
<p><a href="20091224.html">2009-12-24 X'mas eve</a></p>
</tal:block>

```

Again, we want many pages generated from single source code. 20091225.html or 20091224.html are so. You can assign `_default` to those files:

### users/*_*default/blog/*_*default.html

```html
<p>This is a blog entry for ${this/entry_name} by ${this/user_name}.</p>

```

Fill `entry_name` by hook.

### hooks/users/*_*default/blog/*_*default.html.php

```php
<?php
$this->entry_name = $this->pathargs[1]; // The 2nd _default matching element.

```

There are two `_default` in path for URL, so two elements under `users` and under `blog` are stored sequentially in Pinoco's `pathargs` property. User name the first element is already treated in `/_app/hooks/users/_default/_enter.php`. Here, we filled a `entry_name` with the 2nd element which points blog entry name.

You got a base system to multiple contents by minimum coding. Until here, we build a first step of development. For a real blog, you should take some data from database by `user_name` and `entry_name` and display them. I guess, you've already learned to do so if good PHP user. You can choose any database and show real contents in this base app as you like.

Although other many frameworks are using regular expression, routes or such as. They dispatch HTTP request to action class that user implemented. That way is important if you want complete flexible action mapping for URL. But Pinoco's advantage is not there. Think about growing web site which added various contents day a day. Working with regular expression every time is so hard for your contents creators. They can publish their contents  by only uploading TAL based XHTML page like to old static site. This is the transparency of Pinoco!

(Of course, if you don't like modification by others, you can use other framework with Pinoco. e.g. CakePHP is useful for building CMS easier.)
