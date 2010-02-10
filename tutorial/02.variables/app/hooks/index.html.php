<?php
// REMARKS: Local variables are never shown in other scripts or pages.
//          Because each scripts are in separated scope.

$this->info = "";

$localvars = $this->newVars(); // You can write as Pinoco::newVars();

// Several ways to bind variables (looks like JavaScript)
$localvars->foo  = "Foo in vars";
$localvars["bar"] = "Bar in vars";
$localvars->set("baz", "Baz in vars");

// Getting variable names list
$this->info .= sprintf("localvars keys: (%s)\n",
    $localvars->keys()->join(",")
);

// Several ways to get variables value (supporting default value like Python)
$this->info .= sprintf("foo:%s\nbar:%s\nbaz:%s\nbax:%s\nbuzz:%s\n\n",
    $localvars->foo,
    $localvars["bar"],
    $localvars->get("baz"),
    $localvars->get("bux"), // Null or default value such as if a variable not exists.
    $localvars->get("buzz", "Text as default") // default value instead of buzz value
);

$locallist = $this->newList(); // You can write as Pinoco::newList();

$locallist->push("1st");
$locallist->unshift("2nd");
$locallist->concat(array("3rd", "4th"));

$tmp = $this->newList(array("5th", "6th"));  // Copy elements from array to new list.
$locallist->concat($tmp);

$locallist->pop();  // 6th element will be removed

function decorate_with_bracket($e) { return '[' . $e . ']'; }
$locallist = $locallist->map('decorate_with_bracket');

$this->info .= sprintf("locallist(%d items) : %s", $locallist->count(), $locallist->shift());
foreach($locallist as $e) {
    $this->info .= sprintf(",%s", $e);
}
// Of cource, you can use $locallist->join(',') or reduce() for the same purpose.


// All of vars and lists can be operated like native array.
// Also, PHPTAL works with them seamlessly.
// However you can use <del>worst</del> PHP array if you want truly.
