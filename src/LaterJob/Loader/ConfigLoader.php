<?php
namespace LaterJob\Loader;

use LaterJob\Config\DbMetaConfig;
use LaterJob\Config\QueueConfig;
use LaterJob\Config\WorkerConfig;
use Pimple\Container;

/**
  *  Methods used to load the config  
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class ConfigLoader implements LoaderInterface
{
    
    public function parseDatabaseOptions(array $options,DbMetaConfig $meta)
    {
        return $meta->parse($options);
    }
    
    
    public function parseQueueOptions(array $options,QueueConfig $queue)
    {
        return $queue->parse($options);
    }
    
    
    public function parseWorkerOptions(array $options,WorkerConfig $worker)
    {
        return $worker->parse($options);
    }
    
    
    public function boot(Container $queue)
    {
        $queue['config.database'] = $this->parseDatabaseOptions($queue['options'],new DbMetaConfig());
        $queue['config.queue']    = $this->parseQueueOptions($queue['options'],new QueueConfig());
        $queue['config.worker']   = $this->parseWorkerOptions($queue['options'],new WorkerConfig());
        
        return $queue;
    }
    
}
/* End of File */