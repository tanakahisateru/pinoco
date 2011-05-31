Pinoco is a web development framework using PHP and (mainly) PHPTAL.

* Has various usages even in cases OOP based frameworks don't fit.
* Smaller code-base and lightweight footprint ([[PerformanceComparison]])
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

**/htdocs/hello.html**

```html
 <p>Hello <span tal:content="this/message | default">World</span>.</p>
```

**/app/hooks/hello.html.php**

```php
<?php
 $this->message = "Pinoco";
```

