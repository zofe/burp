Burp
============

Simple php __Router__ (or filter?) that can work with "URI", "QUERY STRING", or both.  
It also has a simple __Event__ Listener implementation (to fire or queue application evens).

You can use Rutto in your preferred framework (Including laravel), It does not pretend to be the only router, It just check your urls then fire or queue your events.


## why

the idea is to have a way to _work with your application urls_ and to define a "semantic" in your urls.<br />
and to make widget that works with events fired by uri-segments or query-string  in an easy way.

## Installation

install via composer adding ```"zofe/burp": "dev-master"```



## usage



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


//widget routing
Burp::get('pg/(\d+)', null, array('as'=>'page', function($page) {
    echo "current page is page: $page<br>";
}));

Burp::get(null, 'ord=(-?)(\w+)', array('as'=>'orderby', function($direction, $field) {
    $direction = ($direction == '-') ? "descending" : "ascending";
    echo "current sorting is on : $field ($direction)<br>";
}))->remove('page');



//home route
Burp::get('^/{page?}$', null, array('as'=>'home', function() {

    echo '<hr>';
    echo '<a href="'.Burp::linkRoute('page',1).'">page 1</a><br>';
    echo '<a href="'.Burp::linkRoute('page',2).'">page 2</a><br>';

    echo '<a href="'.Burp::linkRoute('orderby',array('','title')).'">order by title asc</a><br>';
    echo '<a href="'.Burp::linkRoute('orderby',array('-','label')).'">order by label desc</a><br>';

    echo '<hr>';
}));

//404 route
Burp::missing(function() {
    header("HTTP/1.0 404 Not Found");
    echo '404 :: Not Found';
    die;
});

//where all began
Burp::dispatch();
```

