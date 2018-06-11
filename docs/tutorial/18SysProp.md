Pinoco is a iteratable object, so you can look into system properties. Check how Pinoco status is when a page rendered.

### index.html

```html
<table>
<tr tal:repeat="prop this">
    <td>${repeat/prop/key}</td>
    <td>${prop}</td>
</tr>
</table>
```

You can check all properties cleanly.

See details about those:

<table>
<tr><th>Name</th><th>Desc</th><th>Example</th></tr>
<tr><td>baseuri</td><td> Web site home URI </td><td> /18.sysprop</td></tr>
<tr><td>basedir</td><td> Web site directory in file system</td><td> /home/foobar/public_html/18.sysprop</td></tr>
<tr><td>sysdir</td><td> Application directory </td><td> /home/foobar/public_html/18.sysprop/_app</td></tr>
<tr><td>path</td><td> Request path under site home </td><td> / </td></tr>
<tr><td>subpath</td><td> Unprocessed sub-path (for hooks)</td><td></td></tr>
<tr><td>pathargs</td><td> _default matched elements </td><td>Pinoco_List</td></tr>
<tr><td>script</td><td> Running hook script </td><td></td></tr>
<tr><td>activity</td><td> Hook script execution history </td><td>Pinoco_List</td></tr>
<tr><td>page</td><td> Page file for switching view </td><td></td></tr>
<tr><td>autolocal</td><td> Auto local variables </td><td>Pinoco_Vars</td></tr>
<tr><td>directory_index</td><td> Directory index files </td><td> index.html index.php</td></tr>
<tr><td>incdir</td><td> Extra include directories </td><td>Pinoco_List</td></tr>
<tr><td>renderers</td><td> Renderer object mappings for extensions </td><td>Pinoco_Vars</td></tr>
<tr><td>url_modifier</td><td> URL modifier </td><td></td></tr>
<tr><td>page_modifier</td><td> Page resolver modifier </td><td></td></tr>
</table>

Properties shown as Pinoco_Vars or Pinoco_List are can be iterate more deeply.

In hook script execution phase, you can inspect the status.

### _app/hooks/index.html.php
```php
<?php
foreach($this as $key=>$value) {
    echo "$key = $value";
}
```

or

### _app/hooks/index.html.php
```php
<?php
var_dump($this->toArrayRecurse(20));
```

It helps your understanding and debugging Pinoco behavior. If you use FirePHP, toplevel _leave is the best chance to dump this dumped status to Firebug's console.

Note: These properties are reserved strictly. Never assign conflicting name for your property.
