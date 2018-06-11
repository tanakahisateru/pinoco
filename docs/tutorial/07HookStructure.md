In this section, you can learn how to hook for directory structures.

Think about the case: You should create a web site which promotes a product sales company. This company requests to introduce **products** on separated pages each other. And they wanted list of **items** to sale to customers directly on web. And more, they want a secret channel to support their customers.

This site has 3 features which have different contexts each other. This website would be designed as:

* /products/*
* /shop/*
* /secret/*

So, the front page would be:

### index.html

```html
<tal:block>
<p><a href="products/">Our products</a></p>
<p><a href="shop/">Online shop</a></p>
<p><a href="secret/">SECRET</a></p>
</tal:block>
```

Under the *products* directory, you display all products always. Under the *shop* directory, you display all items too.

Ok, before thinking how to do, prepare some HTMLs first.

### products/index.html

```html
<tal:block>
<ul>
    <li tal:repeat="product this/products">${product}</li>
</ul>
<p><a href="p1.html">Product 1</a></p>
<p><a href="p2.html">Product 2</a></p>
</tal:block>
```

### products/p1.html

```html
<tal:block>
<ul>
    <li tal:repeat="product this/products">${product}</li>
</ul>
<p>Product 1</p>
</tal:block>
```

### products/p2.html

```html
<tal:block>
<ul>
    <li tal:repeat="product this/products">${product}</li>
</ul>
<p>Product 2</p>
</tal:block>
```

### shop/index.html

```html
<tal:block>
<ul>
    <li tal:repeat="item this/items">${item/name}</li>
</ul>
<p><a href="i1.html">Item 1</a></p>
<p><a href="i2.html">Item 2</a></p>
</tal:block>
```

### shop/i1.html

```html
<tal:block>
<ul>
    <li tal:repeat="item this/items">${item/name}</li>
</ul>
<p>Item 1</p>
</tal:block>
```

### shop/i2.html

```html
<tal:block>
<ul>
    <li tal:repeat="item this/items">${item/name}</li>
</ul>
<p>Item 2</p>
</tal:block>
```

6 HTMLs for products and items are in your hand. You should provide lists for *products* and *items* to each other.

Will you create 6 hook scripts to each HTML? Please think about management. HTML pages will increase more while running the website. Can you call programmers to write corresponding hooks every time? No, it's not a good idea to modify and copy application logic many time.

At that time, Pinoco supports useful hook methods which executed at "enterring" directory and "leaving" directory.

Two special files `_enter.php` and `_leave.php` in each directories under `_app/hooks` must be executed before/after the time when any resources are accessed under the directory even if they are not hooked by each corresponding hook scripts.

<table>
<tr>
<td>_enter.php</td>
<td>When entering down the directory, it is executed before any document hook script and any `_enter.php` in lower directory.</td>
</tr>
<tr>
<td>_leave.php</td>
<td>When leaving up the directory, it is executed after HTML generated and execution of `_leave.php` in lower directory.</td>
</tr>
</table>

Create `products/_enter.php` to set up the variable commonly used under `products` directory.

### _app/hooks/products/_enter.php

```php
<?php
// Common data in products directory
$this->products = $this->newList(array('Product 1', 'Product 2'));
```

Similarly, set up the variable used under `shop` directory. There are no problem if it is complex because of not configuration but script.

### _app/hooks/shop/_enter.php

```php
<?php
// Common data in shop directory
$this->items = $this->newList();
$this->items->push($this->newVars(array('name' => 'Item 1', 'price' => 100)));
$this->items->push($this->newVars(array('name' => 'Item 2', 'price' => 200)));
```

Completed! We put only these two files. We can be free about maintenance ever. If products or items are added, no more hooks are not needed.
 
Please try to separate products/items list block to macro. So, each documents will be more simple and will be more easy to edit each contents.
 

Note that `_enter.php` and `_leave.php` are nestable. Upper `_enter.php` runs first. If you want to get a properties about products from the common database, you can put `_enter.php` into the root of hooks and prepare your database connection. If your database requires explicit releasing for resources, you can make the last fortress to release in `_leave.php` of the root level.

Here, we try to set HTML's content type and character encoding using top level `_enter.php`. All contents in this sire can be fixed to UTF-8 characters.

### _app/hooks/_enter.php

```php
<?php
$this->header("Content-Type: text/html;charset=utf-8");
// setup resources or configure environment.
```

### _app/hooks/_leave.php

```php
<?php
// cleanup resources or something to do after response.
```

At the real application, top level `_enter.php` will be like global configuration file or such as.


At last, remember the secret contents required. Don't care what service are there, then we put a simple page that has a short text.

### secret/index.html

```html
<p>This is a secret.</p>
```

Let's try to create a security filter that control to show contents according to condition of authorization in user session.

### _app/hooks/secret/_enter.php

```php
<?php
if(!$this->authorized) { // This condition would be failed always.
    $this->forbidden();
}
```

When you try to show some contents in *secret* directory, `secret/_enter.php` is invoked. Here, `$this->authorized` is not initialized, then the condition mus be true always. And `forbidden()` method will be called. It's simplest and transparent to any other contents.

`forbidden()` method cancels the process in this script and later one. Then it returns error code of `403 Forbidden` to the browser. (There are `notfound()` method, they are aliases of `error()`. Deeper script are not executed but `_leave.php` in the same and above directories will be executed.)

Your access would be denied for under *secret*.

Only above you should implement for the easiest security filter. Here, we don't take care to fill `authorized` by how. The most important point in this section is that "It's very easy and direct to implement a security filter".

Because of scripting you can do anything like error page customizing, redirecting to sign-up guide or etc...
