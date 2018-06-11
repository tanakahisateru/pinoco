Pinoco is a web development framework using PHP and (mainly) PHPTAL.

# Abstract
* Has various usages even in cases OOP based frameworks don't fit.
* Smaller code-base and lightweight footprint.
* Explicit and strict View/Logic isolation (each stored into isolated folders).
* Designer friendly.
* Content management workflow similar to that of a static site (Consistency of file paths and URI).
* Seamless design and preview under a local file system.
* Layout macro using TAL like Dreamweaver Library (but SCM friendly unlike Dreamweaver).
* Extreme transparency of design files and source code.
* Easy to introduce to plain PHP users/procedural programmers.
* Easy to apply PHP to a static site.
* High flexibility and less restriction / Freedom would be more powerful than convention or configuration.
* Small single-purpose features, lower training costs
* No restrictions upon using your own libraries.
* No strict need of object oriented programing (OOP Optional).

Pinoco replaces the need for a web framework because it works automatically between the request and response structurally. You may think that a framework should exist as a full stack and force development into formal style, but Pinoco is different. Pinoco has no database support and no scaffolding tools. So, you can assume it as only an "environment". But this environment will be a good fit for many web sites. You can start doing Pinoco application development on a static site which has been built with HTML only. Then you can manage your content in the same way as you would static site.

The name of "Pinoco" comes from "Pinocchio". He was a wooden puppet, but the wood he was made of was enchanted by magic. He could then move by his own free will. Just like in this story, Pinoco will let the file tree in your static site act autonomously.

Though there is no relation to the character "Pinoko" in "Black Jack". If the web creators were to Black Jack, Pinoco(Pinoko) would be great but at best a support character.

Pinoco also stands for "PHP Is Not for Object Coders Only". I hope PHP aiming to OOP world is also to be more easy to use for designers or script hackers.

# Requirements
* PHP 5.1.2 or greater
* Apache
* mod_rewrite 

<small>or Apache alternatives run with FastCGI.</small>

# Install

## PEAR
`pear install http://tanakahisateru.github.io/pinoco/pear/Pinoco-0.8.0.tgz`

## Composer

Put these code into your composer.json and run `php composer.phar install`.

```
{
    ...
    "require": {
        "php":">=5.1.2",
        "pinoco/pinoco":"*",
        "phptal/phptal":">=1.2.1"
    }
}
```

... or manual install as you know.

# Quick start
Copy this [empty project](example/min) to your web site. You can also use [more productive example](example/prod).

**/htdocs/hello.html**

```html
 <p>Hello <span tal:content="this/message | default">World</span>.</p>
```

**/app/hooks/hello.html.php**

```php
<?php
 $this->message = "Pinoco";
```

# Performance
Fastest class (even though productivity and service-ability). See [Performance Comparison](misc/PerformanceComparison.md)

# Changelog
See [[Changelog]]

# Tutorials

* [Setup Project](tutorial/00SetupProject.md)
* [Hello World](tutorial/01HelloWorld.md)
* [Variables](tutorial/02Variables.md)
* [Templating](tutorial/03Templating.md)
* [Resolve URL](tutorial/04ResolveUrl.md)
* [Templating 2](tutorial/05Templating2.md)
* [Hook Document](tutorial/06HookDocument.md)
* [Hook Structure](tutorial/07HookStructure.md)
* [Hook Default](tutorial/08HookDefault.md)
* [Raw Output](tutorial/09RawOut.md)
* [View Switching](tutorial/10ViewSwitch.md)
* [Flow Control](tutorial/11FlowControl.md)
* [Auto Local Variable](tutorial/12AutoLocal.md)
* [Library](tutorial/13Library.md)
* [Raw PHP](tutorial/14RawPhp.md)
* [Configure Tmplate Engine](tutorial/15ConfTmpl.md) 
* [URL Modification](tutorial/16UrlMod.md)
* [Custom Error](tutorial/17CustomError.md)
* [System Properties](tutorial/18SysProp.md)
* [Using Other Frameworks](tutorial/19OtherFw.md)
* [Using Other Tmplate Engines](tutorial/20OtherTmpl.md) 

// TODO Add more tutorials using new features after 0.4.0

You can get all source codes (based on *min* example) [here](example/tutorial).

You can learn the basic way of how to make static contents "transparently" dynamic through this course. Please compare Pinoco's development method with simple PHP pages and/or a full stack application development framework to understand her better.

## See Also

* [PHPTAL FAQ](PHPTAL-FAQ.md)
* [How to use with Nginx](How-to-use-with-Nginx.md)
