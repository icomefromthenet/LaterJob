<?php
namespace LaterJobApi\Provider;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Monolog\Logger,
    Monolog\Handler\StreamHandler;

use Doctrine\DBAL\Connection;

use DBALGateway\Feature\StreamQueryLogger;

use LaterJob\Queue,
    LaterJob\Log\MonologBridge,
    LaterJob\UUID,
    LaterJob\Util\MersenneRandom,
    LaterJob\Loader\ConfigLoader,
    LaterJob\Loader\ModelLoader,
    LaterJob\Loader\EventSubscriber;

use Silex\Application;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
  *  Base exception class for this app
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueueServiceProvider implements ServiceProviderInterface
{
    
    protected $index;
    
    /**
      *  Class Constructor
      *
      *  @access public
      *  @param string $queue_index
      */
    public function __construct($queue_index)
    {
        $this->index = $queue_index;
    }
    
    
    const EVENT_DISPATCHER  = '.event.dispatcher';
    
    const UUID_GENERATOR    = '.uuid.generator';
    
    const QUEUE             = '.queue';
    
    const LOG_BRIDGE        = '.log.bridge';
    
    const LOADER_CONFIG     = '.loader.config';
    
    const LOADER_MODEL      = '.loader.model';
    
    const LOADER_EVENTS     = '.loader.events';
    
    const OPTIONS           = '.options';
    
    const QUERY_LOG         = '.query_log'; 
    
    public function register(Container $app)
    {
        
        $index = $this->index;
        
        $app[$this->index.self::EVENT_DISPATCHER] = function ($name) use ($app) {
            return new EventDispatcher(); 
        };
        
        $app[$this->index.self::UUID_GENERATOR] = function () use ($app)  {
            return new UUID(new MersenneRandom());
        };
        
        $app[$this->index.self::LOG_BRIDGE] = function() use ($app) {
           return $app['monolog'];
        };
        
        $app[$this->index.self::LOADER_CONFIG] = function() use ($app){
            return new ConfigLoader();
        };
        
        $app[$this->index.self::LOADER_EVENTS] = function() use ($app){
            return new EventSubscriber();
        };
        
        $app[$this->index.self::LOADER_MODEL] = function() use ($app){
            return new ModelLoader($app['db']);
        };
        
        $app[$this->index.self::QUEUE] = function($name) use ($app,$index){
            
             $event  = $app[$index.QueueServiceProvider::EVENT_DISPATCHER]; 
             $log    = $app[$index.QueueServiceProvider::LOG_BRIDGE];
             $option = $app[$index.QueueServiceProvider::OPTIONS];
             $uuid   = $app[$index.QueueServiceProvider::UUID_GENERATOR];
             $config = $app[$index.QueueServiceProvider::LOADER_CONFIG];
             $model  = $app[$index.QueueServiceProvider::LOADER_MODEL];
             $events = $app[$index.QueueServiceProvider::LOADER_EVENTS];
             
             
             return new Queue($event,$log,$option,$uuid,$config,$model,$events);
            
        };
        
        $app[$this->index.self::QUERY_LOG] = function() use ($app){
            return new StreamQueryLogger($app['monolog']);
        };
       
    }

    public function boot(Container $app)
    {
       $app[$this->index.self::EVENT_DISPATCHER]->addSubscriber($app[$this->index.self::QUERY_LOG]); 
    }
}
/* End of File */