Burp
============

Simple php __Router__ that can work with "URI", "QUERY STRING", or both.  
It also has a simple __Event__ Listener implementation (to fire or queue application events).

You can use Burp in your preferred framework (Including laravel), It does not pretend to be the only router, It just check your urls then fire or queue your events.


## why

The idea is to have a way to _work with your application urls_ and to define a "semantic" in your urls.<br />
To make widgets that works driven by  uri-segments or query-string, without the need to have a classic controller.

## Installation

install via composer adding ```"zofe/burp": "dev-master"```

if you're using laravel add the service provider in config/app.php:
    
    'providers' => array(
        ...
        'Zofe\Burp\BurpServiceProvider',
    )
    
## usage

Burp is similar to any other PHP router, but it can also behave as a filter.  
There are two main differences you need to know:

  - a route rule can be __strict__ or __not strict__ (It means: "exact match", or "partial match" i.e. a non strict rule can match some uri-serment or some query-string parameter)

  - rules are non-blocking, It means that a single http request can trigger more than one route 
  
```

Burp::$http_method($uri_regex, $querystr_regex, array('as'=>$route_name, function($param) {
    
	//do something
}));

Burp::dispatch();
```

samples

```php

<?php


//catch /user/2 (GET)
Burp::get('^/user/(\d+)$', null, array('as'=>'user.show', function($id) {
    //show user $id
}));

//catch /user (POST)
Burp::post('^/user$', null, array('as'=>'user.create', function() {
    //create new user
}));

//catch /user/2 (PATCH)
Burp::patch('^/user/(\d+)$', null, array('as'=>'user.update', function($id) {
    //save changes for user $id
}));

//catch /something?apikey=xxxx
Burp::get(null, 'apikey=(\w+)', array('as'=>'key', function($key) {
    //check api key in query string..
}));

//will return: /currenturi?apikey=asda
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
//but all uncatched
Burp::missing(function() {
    header("HTTP/1.0 404 Not Found");
    echo '404 - Resource Not Found';
    die;
});

//where all began
Burp::dispatch();
```


## usage - in laravel 

This snippet should give you the idea that you can use Burp to:  
"define some behavior across laravel routes".  

This url: `/article/list?ord=-title` will fire "sort"  event.  
This url: `/article/list/pg/2?ord=title`  will fire "sort" and "page" events.

More, as you know laravel pagination work natively "only" with something like this:  
`/articles/list?page=1`,  but in this sample for this controller It will work via segment:  
`/articles/list/pg/x` (without to create a custom pagination class).


```php

<?php

#in your laravel routes.php add

Route::pattern('pg', 'pg/(\d+)');
Route::get('/articles/list/{pg?}', array('as'=>'art','uses'=>'ArticleController@getList'));

//define some general purpose events on uri-segments
Burp::get('pg/(\d+)', null, array('as'=>'page', function($page) {
     \Event::queue('page', array($page));
}));
//define some general purpose events on query-string
Burp::get(null, 'ord=(-?)(\w+)', array('as'=>'orderby', function($direction, $field) {
    $direction = ($direction == '-') ? "DESC" : "ASC";
    \Event::queue('sort', array($direction, $field));
}))->remove('page');

Burp::dispatch();


#in your controller 
class ArticleController extends BaseController {

public function __construct()
{
    //starting from a clean query builder
    $this->articles = new Article;
    
    //listen for burp defined events
    \Event::listen('sort', array($this, 'sort'));
    \Event::listen('page', array($this, 'page'));
    
    //flush queued events
    \Event::flush('sort');
    \Event::flush('page');
}

public function sort($direction, $field)
{
    $this->articles = $this->articles->orderBy($field, $direction);
}
public function page($page)
{
    \Paginator::setCurrentPage($page);
}

public function getList()
{
    //paginate
    $articles = $this->articles->paginate(20);
    
    //fix links to use custom defined pagination-uri (instead classic 'page=?')
    $links = $articles->links();
    $links = preg_replace('@href="(.*\?page=(\d+))"@U', 
                          'href="'.Burp::linkRoute('page', '$2').'"', $links);

    return View::make('articles.list', compact('articles','links'));
}

}
```

Now you are also free to change your url-semantic in your router, for example switching from query-string to uri-segments or viceversa.  
You can also move events and common behavior in a base controller  and then extend this one

