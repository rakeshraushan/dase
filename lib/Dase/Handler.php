<?php

/*
 * based on Handler class from redberry toolkit:
 * http://redberry.googlecode.com
 *
 */

class Dase_Handler {

    private $request;
    
    function __construct($request) {
        $this->request = $request;
    }

    public static function get($handler = false, $args = array(), $request = false) {
        $request = $request === false ? new Request() : $request;
        if(is_array($handler)) {
            if(count($handler) == 0) return new Handler($request);
            $args = is_array($handler[1]) ? $handler[1] : array();
            $handler = $handler[0];
        }
        Log::debug(__CLASS__.': Getting handler "'.$handler.'" with args: '.implode(', ', $args));
        $handler = $handler === false || !$handler ? 'Handler' : $handler;
        if($reflector = new ReflectionClass($handler)) {
            if($h = $reflector->newInstanceArgs(array_merge(array($request), $args))) return $h;
        }
        //if($h = new $handler($request)) return $h;
        else return new Handler($request);
    }

    public function go() {
        $handler_method = 'do'.ucwords($this->getRequest()->getMethod());
        return $this->$handler_method();
    }

    public function getRequest() {
        return $this->request;
    }

    public function doGet() {
       return new Response('Not Found'."\n", Response::NOTFOUND); 
    }

    public function doPost() {
        return new Response('Not Found'."\n", Response::NOTFOUND);
    }

    public function doPut() {
        return new Response('Not Found'."\n", Response::NOTFOUND);
    }

    public function doDelete() {
        return new Response('Not Found'."\n", Response::NOTFOUND);
    }
}
?>
