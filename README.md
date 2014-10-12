Burp
============

Simple php __Router__ (or filter?) that can work with "URI", "QUERY STRING", or both.  
It also has a simple __Event__ Listener implementation (to fire or queue application events).

You can use Burp in your preferred framework (Including laravel), It does not pretend to be the only router, It just check your urls then fire or queue your events.


## why

The idea is to have a way to _work with your application urls_ and to define a "semantic" in your urls.<br />
To make widgets that works driven by  uri-segments or query-string, without the need to have a classic controller.

## Installation

install via composer adding ```"zofe/burp": "dev-master"```


## usage

```php

<?php

...

Burp::get('^user/(\d+)$', null, array('as'=>'user.show', function($id) {
    //show user $id
}));

Burp::post('^user$', null, array('as'=>'user.create', function() {
    //create new user
}));

Burp::patch('^user/(\d+)$', null, array('as'=>'user.update', function($id) {
    //save changes for user $id
}));

Burp::get(null, 'apykey=(\w+)', array('as'=>'key', function($key) {
    //check api key in query string..
}));

//will return: /currenturi?apykey=asda
Burp::linkRoute('key','asda')

Burp::dispatch();
```


## usage - full example as front-controller



if you need to make a front controller, you can start from this .htaccess

    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
    
and this php file
```php

<?php
#index.php

require_once __DIR__ . '/vendor/autoload.php';

use Zofe\Burp\Burp;


//widget routing - fired when url is for example:  /something/pg/2
Burp::get('pg/(\d+)', null, array('as'=>'page', function($page) {
    echo "current page is page: $page<br>";
}));

//widget routing - fired when url is for example: /something?ord=-title
Burp::get(null, 'ord=(-?)(\w+)', array('as'=>'orderby', function($direction, $field) {
    $direction = ($direction == '-') ? "descending" : "ascending";
    echo "current sorting is on : $field ($direction)<br>";
}))->remove('page');



//strict route  - fired when uri is "/"  or "/pg/2", but not when is "/something/pag/2" ...
Burp::get('^/{page?}$', null, array('as'=>'home', function() {

  echo '<hr>';
  echo '<a href="'.Burp::linkRoute('page',1).'">page 1</a><br>';
  echo '<a href="'.Burp::linkRoute('page',2).'">page 2</a><br>';

  echo '<a href="'.Burp::linkRoute('orderby',array('','title')).'">sort title up</a><br>';
  echo '<a href="'.Burp::linkRoute('orderby',array('-','label')).'">sort label down</a><br>';

  echo '<hr>';
}));

//404 route  - fired only if there are defined strict routes (i.e.: ^/$ or ^.*$)  
//but all uncached
Burp::missing(function() {
    header("HTTP/1.0 404 Not Found");
    echo '404 - Resource Not Found';
    die;
});

//where all began
Burp::dispatch();
```

