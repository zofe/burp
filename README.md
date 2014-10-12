Rutto
============

Simple php __Router__ (or filter?) that can work with "URI", "QUERY STRING", or both.  
It also has a simple __Event__ Listener implementation (to fire or queue application evens).

You can use Rutto in your preferred framework (Including laravel), It does not pretend to be the only router, It just check your urls then fire or queue your events.


## why

the idea is to have a way to _work with your application urls_ and to define a "semantic" in your urls.<br />
To do an example, think for example about "pagination" and "sorting" results. 
Usually  a pagination is implemented using 
It's more correct to define over and over again this params/segments for each route where you need this behavior or just one time?

It's more correct 


90% of router I've seen works with "HTTP METHODS" and "URI", the query string has been forgotten.  
Ok you can generally do something with `$_GET`  in your controllers or filters,  but there isn't nothing that can "match" __uri-segments__ and __get-params__ in the same way, at the same time.

Rutto can do that.


## Installation

install via composer adding ```"zofe/rutto": "dev-master"```



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

$rutto = new Zofe\Rutto();

//
Zofe\Rutto::get(null, 'show=(\d+)', function($id) {
    echo "Ok you want to see {$id}";
    
});




```
if you don't like commas as separator or "masset" as first segment you can easily change the configuration.



### kudos to 
- Tubal Martin for https://github.com/tubalmartin/YUI-CSS-compressor-PHP-port
- Douglas Crockford for JSMin
and a lot of people that worked on both project.
 
