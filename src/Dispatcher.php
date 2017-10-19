<?php
/**
 * Created by PhpStorm.
 * User: nvanhaezebrouck
 * Date: 06/10/2017
 * Time: 16:25
 */

namespace VekaServer\Dispatcher;

use GuzzleHttp\Psr7\Response;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Dispatcher implements RequestHandlerInterface {


    private $middlewares = [];

    /**
     * @var Response
     */
    private $response;

    /**
     * @var int
     */
    private $index = 0;

    public function __construct()
    {
        $this->response = new Response();
    }

    /**
     * Permet d'enregistrer un nouveau middleware
     * @param callable|MiddlewareInterface $middleware
     * @return Dispatcher
     */
    public function pipe($middleware):Dispatcher {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Permet d'executer les middlewares
     * @param ServerRequestInterface $request
     * @return mixed
     */
    public function process(ServerRequestInterface $request):ResponseInterface {

        // recuperation du prochain middleware
        $middleware = $this->getMiddlewares();
        $this->index++;

        // si PSR-7
        if(is_callable($middleware)){
            return $middleware($request, $this->response, [$this, 'process']);
        }
        // si PSR-15
        else if($middleware instanceof MiddlewareInterface){
            return $middleware->process($request, $this);
        }
        else {
            return $this->response;
        }

    }

    /**
     * Permet de recuperer le prochain middleware
     * @return callable|MiddlewareInterface
     */
    private function getMiddlewares(){

        if(isset($this->middlewares[$this->index])){
            return $this->middlewares[$this->index];
        }

        return null;

    }

    /**
     * permet de recuperer la reponse de la requete
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request):ResponseInterface
    {
        return $this->process($request);
    }


    /**
     * Send an HTTP response
     *
     * @return void
     */
    public function send(ResponseInterface $response)
    {
        $http_line = sprintf('HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        header($http_line, true, $response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }

        $stream = $response->getBody();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        while (!$stream->eof()) {
            echo $stream->read(1024 * 8);
        }
    }

}