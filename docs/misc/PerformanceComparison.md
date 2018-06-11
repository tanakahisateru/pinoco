Performance comparison with other PHP frameworks.

* 2.4GHz Core2 Duo
* MacOS10.6
* Apache2.2
* PHP5.3

# Hello World test

Benchmark test result for applications to show "Hello World" text only with various frameworks.

```html
<p>Hello World.</p>
```

All applications are optimized to pass unused features like database setup or such as.

This table shows average duration to perform request with parallel 10 connections.

<table>
<tr><th>Framework</th><th>without APC</th><th>with APC</th></tr>
<tr><td>PHP only</td><td>0.363ms</td><td>0.359ms</td></tr>
<tr><td>CakePHP</td><td>31.094ms</td><td>11.042ms</td></tr>
<tr><td>CodeIgniter</td><td>5.510ms</td><td>2.157ms</td></tr>
<tr><td>Pinoco(with PHPTAL)</td><td>6.239ms</td><td>2.573ms</td></tr>
<tr><td>Pinoco(without PHPTAL)</td><td>3.755ms</td><td>1.890ms</td></tr>
</table>

Symfony ... not tested but must be worst, I guess.

See(Japanese): [[http://d.hatena.ne.jp/tanakahisateru/20100908/1283945076]]


# Template engine performance

Template engine performance comparison with real layout and data.

Test program fills 4 elements having name and phone number properties into table tag structure with sanitizing.

```html
<html>
  <head>
    <title>The title value</title>
  </head>
  <body>
    <h1>The title value</h1>
    <table>
      <thead>
        <tr><th></th><th>Name</th><th>Phone</th></tr>
      </thead>
      <tbody>
        <tr>
            <td>0</td>
            <td>foo</td>
            <td>01-344-121-021</td>
        </tr>
        <tr>
            <td>1</td>
            <td>bar</td>
            <td>05-999-165-541</td>
        </tr>
        <tr>
            <td>2</td>
            <td>baz</td>
            <td>01-389-321-024</td>
        </tr>
        <tr>
            <td>3</td>
            <td>quz</td>
            <td>05-321-378-654</td>
        </tr>
      </tbody>
    </table>
  </body>
</html>
```

This table shows average duration to perform request.

<table>
<tr><th>Engine</th><th>Sequential w/o APC</th><th>Parallel w/o APC</th><th>Sequential with APC</th><th>Parallel with APC</th></tr>
<tr><td>HTML</td><td>0.314ms</td><td>0.232ms</td><td>n/a</td><td>n/a</td></tr>
<tr><td>PHP</td><td>0.852ms</td><td>0.554ms</td><td>0.632ms</td><td>0.406ms</td></tr>
<tr><td>Smarty</td><td>4.491ms</td><td>2.512ms</td><td>1.160ms</td><td>0.682ms</td></tr>
<tr><td>PHPTAL</td><td>5.122ms</td><td>2.848ms</td><td>2.033ms</td><td>1.146ms</td></tr>
</table>

See(Japanese): [[http://d.hatena.ne.jp/tanakahisateru/20100906/1283766620]]