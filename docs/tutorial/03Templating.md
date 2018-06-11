In this sectin, you can learn about applying PHPTAL for Web page design.

Whole of pages in a web sites have common layout design to display its identity in regular case. But plain HTML is not designed to do as so. When you want to share a common layout with your all pages, you are forced to copy a certain tag structure to all.

Adobe Dreamweaver is one of editors which has a tag copy management feature. However, when you modify a common element, it will modify all files you have. So, it might be defeat to merge with parallel works from other workspace.

In the other hand, text insertion at server side such as SSI or PHP's include is possible to minimize effects of change, but source code of page design are to be partial and can't keep form as clean XHTML code. For example, one file is leaved tag open and other file starts closing tag first.

Don't worry about. There is PHPTAL!

Create `index.html` and write into it as:

```html
<html metal:use-macro="_wrapper.html/all">
<body>
    <div metal:fill-slot="main">
        <p>My contents are here.</p>
    </div>
</body>
</html>
```

Look into `metal:use-macro` attribute. If you know PHPTAL, as you know, this is the way to reuse a series of tag like as library. The contents (else other metal attributed tag) inside of `metal:use-macro` is a dummy for designing. This HTML means "Expand tag structure defined as `all` in `_wrapper.html`" file and apply to `<html>` element of this document".

"To my `<html>`!? Whole of my contents are overwritten by others?": Don't worry. Though `metal:use-macro` replaces inside with a macro, but you can show your contents using `metal:fill-slot` inside of area defined by `metal:define-slot` in layout macro.

Don't worry, it's not difficult to understand when you create `_.wrapper.html`:

```html
<html metal:define-macro="all">
<head>
<title>Pinoco Tutorial</title>
</head>
<body>
    <div metal:define-slot="main">
        <p>Under construction...</p><!--! Default contents for main area -->
    </div>
</body>
</html>
```

No PHP hook script are needed. Visit your site and see result which looks two files mixed code.

```html
<html>
<head>
<title>Pinoco Tutorial</title>
</head>
<body>
    <div>
        <p>My contents are here.</p>
    </div>
</body>
</html>
```

Look closely at the relationship between tags having "metal:", and study again.

After your agreement, we try real design.

### _wrapper.html

```html
<html metal:define-macro="all">
<head>
<title>${title} - Pinoco Tutorial</title>
<!-- global css and js -->
<link href="css/common.css" rel="stylesheet" type="text/css" />
<tal:block metal:define-slot="exhead">
</tal:block>
</head>
<body align="center">
<div class="wrapper">
    
    <div class="header">
        <div>${title} - Pinoco Tutorial</div>
    </div>
    
    <div class="subpane"><tal:block metal:define-slot="subpane">
        <p>No text here</p><!--! Default contents for sub pane -->
    </tal:block></div>
    
    <div class="main"><tal:block metal:define-slot="main">
        <p>Under construction...</p><!--! Default contents for main area -->
    </tal:block></div>
    
    <div class="footer">Default footer line</div>
</div>
</body>
</html>
```

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
    
    <h1>main</h1>
    <div metal:fill-slot="main">
        <p>My contents are here.</p>
        <p>My contents are here.</p>
        <p>My contents are here.</p>
        <p>My contents are here.</p>
    </div>
    
    <h1>subpane</h1>
    <div metal:fill-slot="subpane">
        <ul class="powerdby">
            <li>Powerd by PHP</li>
            <li>Powerd by Pinoco</li>
        </ul>
    </div>
    
</body>
</html>
```

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
.subpane {
    float:right;
    border:solid 1px gray;
    margin:0.5em 1em 0.5em 1em;
    padding:0.4em;
}
.main {
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

Every file keeps standard form. Each of HTML files on your browser are displayed correctly as: *layout without contents* and *contents without layout*.

Common style is controled by `_wrapper.html`. Additional style in head tag of `index.html` would be inserted into `exhead` block of `_wrapper.html`.

Structure built by header, footer, main content and sub content is defined with `_wrapper.html`. These complex code are invisible from `index.html`. You should fill only `main` and `subpane` block to complete `index.html'.

Note ordering of `main` and `subpane` transposed. In regular case, optional block appears first in HTML code, but you can write main article first and write optional contents later.

`_wrapper.html` uses a variable named '${title}'. It is defined at begging of `index.html` as `tal:define="title 'FrontPage'"`.

Output:
```html
<html>
<head>
<title>FrontPage - Pinoco Tutorial</title>
<!-- global css and js -->
<link href="css/common.css" rel="stylesheet" type="text/css"/>
<!-- local css and js -->
<style type="text/css" rel="">
.powerdby { font-size:70%; }
</style>
</head>
<body align="center">
<div class="wrapper">
    
    <div class="header">
        <div>FrontPage - Pinoco Tutorial</div>

    </div>
    
    <div class="subpane"><div>
        <ul class="powerdby">
            <li>Powerd by PHP</li>
            <li>Powerd by Pinoco</li>
        </ul>
    </div></div>
    
    <div class="main"><div>

        <p>My contents are here.</p>
        <p>My contents are here.</p>
        <p>My contents are here.</p>
        <p>My contents are here.</p>
    </div></div>
    
    <div class="footer">Default footer line</div>

</div>
</body>
</html>
```

Of course, this is not all about PHPTAL, but you will find the most efficiency point about designing with this way. Changing on whole of your site will never occur when you update copyright text in page footer. All HTML keep formal syntax and become more tender for HTML editor. Now, each responsibility are isolated by each files explicitly.

Let's create various more pages using `_wrapper.html` !