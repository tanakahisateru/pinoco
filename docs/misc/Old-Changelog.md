## 0.7.0
* Composer support (current alpha-5)
* PHP5.4's built-in server support
* Pinoco_Pagination smart pagination framework added
* Pinoco::abortIfNotModified() to send status code 304 added
* Pinoco::setcookie() added (can be used for CLI test)
* Pinoco_Vars::values added
* Pinoco_ValidatorContext::all() and any() added (for array form parameter)
* Pinoco_HttpRequestVars::method added
* Several bugfix for Pinoco::serveStatic() and Pinoco::redirect()

## 0.6.1
* Bugfix: error handler crashed only on http. it was not tested...

## 0.6.0
* Functional test support using PHPUnit.
  * $env = Pinoco::testenv(...)->initBy($callback); $pinoco = $env->create($path);
  * Pinoco::header() instead of header()
  * $pinoco->request->* instead of super-globals
* Testing framework changed to PHPUnit.
* Refactored according to PSR-0 closer policy (PHPTAL-1.2.2 like autoloading).
* Pinoco_Validator::emptyResult() to use as initial state of HTML form.
* Pinoco_Vars::registerAsMethod($callback). / 1st argument of $callback points to owner instance.
* Pinoco::config($prop, $file) which accepts .php (returns an array) or .ini (sectioned)
* Pinoco::subscript($script) invokes a nested hook script in isolated variable scope.
* Pinoco::serveStatic($file) can send a file with 304 Not Modified support.
* Empty-projects and tutorials based on more real/useful structure.
* Bugfix.

## 0.5.2
* Bugfix: Pinoco::terminate() or error() failed under eAccelerator. (eAccelerator still has try-catch bug.)

## 0.5.1
* Bugfix: Crashed using with PHPTAL-1.2.1.

## 0.5.0
* License changed to MIT.
* Pinoco_Vars::count() added.
* Pinoco_Vars::markAsDirty() added.
* Pinoco::newPDOWrapper() deprecated.
* PHPTAL syntax extended -- pal:content-nl2br / pal:replace-nl2br
* PHPTAL syntax extended -- pal:attr
* Pinoco_Validator added.

## 0.4.0
* Bug fix.
* Unit tests using Lime.
* Pinoco_Vars::toArrayRecurse() added.
* Pinoco::page_modifier refined.
* Pinoco_Vars::registerAsDynamic() and registerAsLazy() added.
* Pinoco_Vars::toArrayRecurse() added.
* Pinoco_PDOWrapper added.
* Errors and Exceptions in hook scripts displayed fine.