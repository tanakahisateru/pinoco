Let's create a real website after learning [[Tutorial03Templating ]] and [[Tutorial04ResolveUrl]]. In this section, we are based on previous two sections.

The training website has 3 pages. Each pages has global navigation to jump every contents directly. All pages shares common layout design including the navigation menu.

* index.html
* sub/index.html
* sub/sibling.html

At first, write a shared design template as `_wrapper.html` based on [[Tutorial03Templating]]. A navigation bar area is added to previous version.

### _wrapper.html
```html
<html metal:define-macro="all">
<head>
<title>${title} - Pinoco Tutorial</title>
<!-- global css and js -->
<link href="css/common.css" rel="stylesheet" type="text/css"
    tal:attributes="href url:/css/common.css"/><!--! URL resolver works well in master template! -->
<tal:block metal:define-slot="exhead">
</tal:block>
</head>
<body align="center">
<div class="wrapper">
    
    <div class="header">
        <div>${title} - Pinoco Tutorial</div>
    </div>
    
    <ul class="hnav">
        <li><a href="index.html" tal:attributes="href url:/">Home</a></li>
        <li><a href="sub/index.html" tal:attributes="href url:/sub/">Sub</a></li>
        <li><a href="sibling/sibling.html" tal:attributes="href url:/sub/sibling.html">Sibling of Sub</a></li>
    </ul>
    
    <div class="subpane"><tal:block metal:define-slot="subpane">
        <p>No text here</p><!--! Default contents for sub pane -->
    </tal:block></div>
    
    <div class="main"><tal:block metal:define-slot="main">
        <p>Under construction...</p><!--! Default contents for main area -->
    </tal:block></div>
    
    <div class="footer">Default footer line</div>
</div>
</body>
```

You are using a css above, create it too.

### css/common.css
```css
body {
    margin:0px;
    padding:0px;
    text-align:center;
    background-color:gray;
}
.wrapper {
    width:800px;
    margin:auto;
    text-align:left;
    background-color:white;
    padding-bottom:0.5em;
}
.header {
    background-color:#0D8;
    padding:0.4em;
    color:white;
    font-size:200%;
}
.hnav {
    margin:0px;
    padding:4px 0px;
    background-color:black;
}
.hnav li {
    display:inline;
    margin:0px;
}
.hnav li a {
    text-align:center;
    font-weight:bold;
    text-decoration:none;
    padding:8px;
    color:white;
}
.hnav li a:hover {
    text-decoration:underline;
}
.subpane {
    float:right;
    border:solid 1px gray;
    margin:0.5em 1em 0.5em 1em;
    padding:0.4em;
}
.main {
    clear:left;
    margin:0.4em 1em 1em 1em;
}
.footer {
    clear:both;
    font-size:80%;
    margin:0.5em 2em;
    text-align:center;
    color:gray;
}
```

Look into a reference to CSS and navigation bar (class="hnav" attached) of `_wrapper.html`. This design template will be used from various URL in the website. So, you must let it keep URL consistence always.  This is most popular usage of URL resolver you know at [[Tutorial04ResolveUrl]].

You can render this file under local files system using into browser correctly. But it's invisible to the web. The file beginning underscore is hidden files to use as private member. So, there are still no contents. Let's implement real contents now.

### index.html
```html
<html metal:use-macro="_wrapper.html/all" tal:define="title 'FrontPage'">
<head>
<tal:block metal:fill-slot="exhead">
<!-- local css and js -->
<style type="text/css" rel="">
.powerdby { font-size:70%; }
</style>
</tal:block>
</head>
<body>
    <div metal:fill-slot="main">
        <h1>Site Root /</h1>
        <p>My contents are here.</p>
    </div>
    <div metal:fill-slot="subpane">
        <ul class="powerdby">
            <li>Powerd by PHP</li>
            <li>Powerd by Pinoco</li>
        </ul>
    </div>
</body>
</html>
```
(

### sub/index.html

```html
<html metal:use-macro="_wrapper.html/all" tal:define="title 'SubPage'">
<head>
</head>
<body>
    <div metal:fill-slot="main">
        <h1>Sub /sub/index.html</h1>
        <p>My contents are here.</p>
    </div>
</body>
</html>
```

### sub/sibling.html

```html
<html metal:use-macro="_wrapper.html/all" tal:define="title 'SubPage sibling'">
<head>
</head>
<body>
    <div metal:fill-slot="main">
        <h1>Sibling of sub /sub/sibling.html</h1>
        <p>My contents are here.</p>
    </div>
</body>
</html>
```

Filling slots defined in macro is optional. Contents inside slot definition is default when not filled by client. On case `sub/index.html` and `sub/sibling.html`, `subpane` is leaved as not having credit text and only `main` slot is filled.

Check that it is perfectly structured 3 page website using your browser. And also, check what HTMLs are generated. And return back to your text editor, confirm the advantage of the low redundancy and readability of your source code.

It's Pinoco's basic pattern to design Web site.

Drastic changing of your thinking is not required. Pinoco's way is simply extended line of static site design. So it provides just only things you wanted for a long time.