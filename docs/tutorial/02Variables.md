In this section, you can learn a useful collection variable. Below contents are actually for programmers, so you may pass if designer.

PHP's native array so-called "paved road to hell" is. Tenderness of not to worry about pointer (not capsuled bat not exists) will take programmers to the hell of array_ functions and references soon. PHP's array disables your effort of growing up to good programmer.

For this unproductive side of PHP, Pinoco provides two alternative collection class. Ah, not to be worry, you can do your work with native arrays even if you pass to learn about these classes. However, I prefer you to learn skills below as soon as possible.

At first, start writing your hook as:

```php
<?php
$this->info = "";
```

## Vars (named variables set)

Vars is a wrapper to use array as hash table. Vars is similar to Javascript's Object type because it looks like hash table and object also.

You can create it in hook script's root scope:

```php
$localvars = $this->newVars();
```

or in any scope:

```php
$localvars = Pinoco::newVars();
```

You can create it from another array:

```php
$localvars2 = Pinoco::newVars(array(
    'foo'=>1,
    'bar'=>2
));
```

Generated object is to be cloned in this way. 

In the other hand, you can wrap already existing PHP array (e.x. '$_GET' or such as)  in this way:

```php
$localvars3 = Pinoco::wrapVars($_GET);
```

Generated object shares content with referenced variables.

There are various methods to store a value into Vars.

```php
$localvars->foo  = "Foo in vars";
$localvars["bar"] = "Bar in vars";
$localvars->set("baz", "Baz in vars");
```

You can get the same result in any way. Write your code for readability case by case.

You can get keys list using `keys()` method.

```php
$this->info .= sprintf("localvars keys: (%s)\n",
    $localvars->keys()->join(",")
);
```

`keys()` method returns List later. Here, you should learn only that `foo`, `bar` and `baz` are stored in `$localvars`.

Vars does not block you even If you fetch an non-existing value. NULL or some default value are returned instead at that time.

```php
$this->info .= sprintf("foo:%s\nbar:%s\nbaz:%s\nbax:%s\nbuzz:%s\n\n",
    $localvars->foo,
    $localvars["bar"],
    $localvars->get("baz"),
    $localvars->get("bux"), // Null or default value such as if a variable not exists.
    $localvars->get("buzz", "Text as default") // default value instead of buzz value
);
```

Make sure that `foo`, `bar` and `baz` are stored. Fetching methods are just corresponding to storage. But trailing `bux` and `buzz` are still not sored. When you are willing to get `bux`, NULL or default value defined by `setDefault()` method is returned. These "default for non-existing" value might be given instantly as 2nd argument of `get()`.

This easy way for default processing is so useful to keep number of lines lessor when you use set of variables which cannot be expected to have some properties: `$_POST` wrapped with Vars, external configuration file, or such as.

## Dynamic / Lazy field

Pinoco's Vars object supports dynamic fields. (since 0.4.0)

You can add dynamic evaluated field by `registerAsDynamic`. The 1st argument is name of field, and 2nd argument is callable object to evaluate a corresponding field value.

```php
function dynamicFieldImpl($owner) {
    $owner->cc1++; // will called many times.
    return $owner->cc1 . " times called (dynamic)";
}
$localvars->cc1 = 0;
$localvars->registerAsDynamic('dynamicField', 'dynamicFieldImpl');
$this->info .= $localvars->dynamicField . "\n"; // => 1 times called (dynamic)
$this->info .= $localvars->dynamicField . "\n"; // => 2 times called (dynamic)
```

If you are using PHP5.3 or greater, you can write also as:
```php
$cc1 = 0;
$localvars->registerAsDynamic('dynamicField', function() use(&$cc1) {
    $cc1++;
    return $cc1 . " times called (dynamic)";
});
```
Quite smart!

Same as dynamic field, lazy field can be added by the same way.

```php
function lazyFieldImpl($owner) {
    $owner->cc2++; // will called once.
    return $owner->cc2 . " times called (lazy)";
}
$localvars->cc2 = 0;
$localvars->registerAsLazy('lazyField', 'lazyFieldImpl');
$this->info .= $localvars->lazyField . "\n";   // => 1 times called (lazy)
$this->info .= $localvars->lazyField . "\n\n"; // => 1 times called (lazy)
```

Lazy value would be evaluated on the first fetching, and it is cached for repeated fetching.

Dynamic value is good for the other formatted data according to a certain field. e.g. Readable date based on timestamp. Ad-hoc PHPTAL's modifier violate your global scope. Instead, let data be able to resolve format conversion by this technique.

Lazy value is good to fetch related data model from database later. You may not use all model structure trough every requests. You can load automatically only when the filed used in your view. 

## List (unnamed ordered values set)

Pinoco's List wraps PHP's array as pure array which saves order of elements. Its API looks like modern Javascript's Array.

You can create it in hook script's root scope:

```php
$locallist = $this->newList();
```

or in any scope:

```php
$locallist = Pinoco::newList();
```
You can create it from another array and can wrap existing array in the same way as Vars:

```php
$locallist2 = Pinoco::newList(array(1, 2, 3));
$locallist3 = Pinoco::wrapList($existing_array);
```

To store a value, use `push()`(add to tail) and `unshift()`(insert into head) method. `concat()` method joins each elements in other List or PHP array to after the last element of itself.

```php
$locallist->push("1st");
$locallist->unshift("2nd");
$locallist->concat(array("3rd", "4th"));

$tmp = $this->newList(array("5th", "6th"));
$locallist->concat($tmp);
```

You can use both `count()` method and PHP native `count()` function to know number of elements.

```php
$this->info .= sprintf("locallist(%d items) :", $locallist->count());
// You can use count($locallist) also.
```

Functional programing is supported like below. You can pass a inline closure to `map()` method if you have PHP5.3.

```php
function decorate_with_bracket($e) { return '[' . $e . ']'; }
$locallist = $locallist->map('decorate_with_bracket');
```

To extract value, use `pop()` (remove from tail: corresponds to `push()`) and  `shift()` (remove from head: corresponds to `unshift()`).

```php
$locallist->pop();
$this->info .= sprintf("%s", $locallist->shift());
```

List can be iterate naturally. This code joins all element of `$locallist` with comma. (Although, this is redundant program because List has `join()` method to return the same result of this.)

```php
foreach($locallist as $e) {
    $this->info .= sprintf(",%s", $e);
}
```

Of course, Vars can be applied to foreach-as syntax as well, try it later if you are interested in.

## Show result

Now, test the crap written various operations to work as intended. Create an HTML document corresponding your hook (`index.html`) as below, and click your browser's reload button.

```php
<pre>${this/info}.</pre>
```

I got these result in my hand.

```php
<pre>localvars keys: (foo,bar,baz)
foo:Foo in vars
bar:Bar in vars
baz:Baz in vars
bax:
buzz:Text as default

locallist(5 items) : [2nd],[1st],[3rd],[4th],[5th].</pre>
```

On browsers of some programmers who are tending to study more, it would not be shown as is. But rather I hope to try various writing way with Pinoco's API document and to get many result by you. Pinoco's collection has other useful methods. Try more.

# Another reason why we use Vars

I tell you about the reason why we should use Vars class instead of plain array. At previous section, I mentioned about PHPTAL's strict variable check. But so strict environment maybe not convenient for dynamic language oriented programmer.

Vars has `setLoose()` method. If `TRUE` would be passed to this method, Vars answers always yes for checking about property existence. What a loose behavior is it. "Ah, you ask about 'message' variable? Ya, I have it, I have anything you want. But I can give you NULL so many time, sorry. :-)" Then PHPTAL shows NULL or such as instead of system error.

And more, to tell the truth, it's important that Pinoco used as `$this' is extended class from Vars. Pinoco instance can also be a loose variables holder.

In case when designers are working more than programmers, if they would anger to error message about variable existence, programmer can provide a simple script below for them instead of sincere approach like as "filling all properties of Pinoco to let it be quiet".

```php
<?php
$this->setLoose();
```
This is effective especially in `_app/hooks/_enter.php`. Details of `_enter.php` can be found at later section.

Of course even though not for properties not to be hold by `$this` directly, you can let it be loose. Your application's logic does not block designer's work. Loose collection fits to strict view technology like PHPTAL.