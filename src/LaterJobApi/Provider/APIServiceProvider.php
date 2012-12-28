<?php
namespace LaterJobApi\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface;

/**
  *  Base exception class for this app
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class APIServiceProvider implements ServiceProviderInterface
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
    
    public function register(Application $app)
    {
        $index = $this->index;
        #------------------------------------------------------------------
        # Load the Entity Formatters
        #
        #------------------------------------------------------------------
        
        $app['laterjob.api.formatters.job'] = $app->share(function() {
            return new  \LaterJobApi\Formatter\JobFormatter();
        });
        
        $app['laterjob.api.formatters.activity'] = $app->share(function() {
            return new \LaterJobApi\Formatter\ActivityFormatter();
        });
        
        $app['laterjob.api.formatters.monitor'] = $app->share(function(){
            return new \LaterJobApi\Formatter\MonitorFormatter();
        });
        
        #------------------------------------------------------------------
        # Setup Routes / Controllers
        #
        #------------------------------------------------------------------

        $app->mount('/queue',  new \LaterJobApi\Controllers\QueueProvider($index));
        $app->mount('/queue',  new \LaterJobApi\Controllers\ActivityProvider($index));
        $app->mount('/queue',  new \LaterJobApi\Controllers\MonitorProvider($index));
        $app->mount('/queue',  new \LaterJobApi\Controllers\ScheduleProvider($index));
       
    }

    public function boot(Application $app)
    {
        
    }
}
/* End of File */