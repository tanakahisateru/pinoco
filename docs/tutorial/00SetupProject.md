In this series of tutorial, I assume that Pinoco has been installed using PEAR. Also these conditions are expected: PHP is installed as Apache module and you can use mod_rewrite in your `.htaccess`. If not so but you can use Pinoco by small adjustment for your bootstrap files(`_gateway.php` and `.htaccess`) or Apache configuration files. Sorry not to tell you that way below.

At first, you should have a folder for your web site. Go the web public folder and create new folder (for example, `01.helloworld` for the 1st session, here) by command or file-manager as you like.

```sh
$ mkdir 01.helloworld

```

Download an empty Pinoco project archive and unpack it into your folder.

```sh
$ cd 01.helloworld
$ wget http://pinoco.googlecode.com/files/empty-project-min.tgz
$ tar xzf empty-project-min.tgz

```

Then, file layout is to be :

<pre>
01.helloworld
  _app
    hooks/*
    lib/*
    .htaccess
  .htaccess
  _gateway.php
</pre>

*01.helloworld* is a web public folder. Static resource or HTML pages are stored just like as static site structure. Basically you can assume that contents in this folder (but else `_app`) are visible to Web as is.

*_app* is an application logic related folder. You will put PHP scripts and also any additional file/folder into here freely. The *_app* and under here is just for web invisible resource.

To understand step by step, remove annoying files included as default in my example project. These files are useful to develop a real site, but they are optional.

```sh
$ rm app/hooks/*.php

```

Now, look into the site using your web browser. For example:

  http://localhost/01.helloworld/

'403 Forbidden' would be shown, it means that you are ready to start. In addition, you can confirm by looking into HTTP response header. Pinoco would be added to 'Powered-By' entry. ( Pinoco's contents often looks a static content or plain PHP page. Then this marking is useful to know if Pinoco works or not.)

Now, blown soul into your static site in Pinoco's way.

## What's wrong?

You may need to set RewriteBase of mod_rewrite. In case an error shown, edit `htdocs/.htaccess` and set RewriteBase as the absolute path of your project URI. (It happens when Alias directive points your folder in Apache configuration.)

```apache
RewriteBase /your/alias/uri

```

