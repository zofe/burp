<?php

if ( ! function_exists('route_pattern')) {
    function route_pattern($name, $regex) {
        return Zofe\Burp\Burp::pattern($name, $regex);
    }
}

if ( ! function_exists('route_get')) {
    function route_get($uri, $parameters) {
        return Zofe\Burp\Burp::get($uri, null, $parameters);
    }
}

if ( ! function_exists('route_post')) {
    function route_post($uri, $parameters) {
        return Zofe\Burp\Burp::post($uri, null, $parameters);
    }
}

if ( ! function_exists('route_patch')) {
    function route_patch($uri, $parameters) {
        return Zofe\Burp\Burp::patch($uri, null, $parameters);
    }
}

if ( ! function_exists('route_put')) {
    function route_put($uri, $parameters) {
        return Zofe\Burp\Burp::put($uri, null, $parameters);
    }
}

if ( ! function_exists('route_delete')) {
    function route_delete($uri, $parameters) {
        return Zofe\Burp\Burp::delete($uri, null, $parameters);
    }
}

if ( ! function_exists('route_any')) {
    function route_any($uri, $parameters) {
        return Zofe\Burp\Burp::any($uri, null, $parameters);
    }
}

if ( ! function_exists('route_head')) {
    function route_head($uri, $parameters) {
        return Zofe\Burp\Burp::head($uri, null, $parameters);
    }
}

if ( ! function_exists('route_missing')) {
    function route_missing( $closure) {
        return Zofe\Burp\Burp::missing( $closure);
    }
}

if ( ! function_exists('route_query')) {
    function route_query($qs, $parameters) {
        return Zofe\Burp\Burp::any(null, $qs, $parameters);
    }
}

if ( ! function_exists('route_dispatch')) {
    function route_dispatch() {
        return Zofe\Burp\Burp::dispatch();
    }
}

if ( ! function_exists('is_route')) {
    function is_route($name, $parameters = array() ) {
        return return Zofe\Burp\Burp::isRoute($name, $parameters);
    }
}

if ( ! function_exists('link_route')) {
    function link_route($name, $parameters = array()) {
        return Zofe\Burp\Burp::linkRoute($name, $parameters);
    }
}

