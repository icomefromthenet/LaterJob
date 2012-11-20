<?php
namespace LaterJob\Loader;

use LaterJob\Config\DbMeta;
use LaterJob\Config\Queue;
use LaterJob\Config\Worker;
use Pimple;

/**
  *  Methods used to load the config  
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class ConfigLoader implements LoaderInterface
{
    
    public function parseDatabaseOptions(array $options,DbMeta $meta)
    {
        return $meta->parse($options);
    }
    
    
    public function parseQueueOptions(array $options,Queue $queue)
    {
        return $queue->parse($options);
    }
    
    
    public function parseWorkerOptions(array $options,Worker $worker)
    {
        return $worker->parse($options);
    }
    
    
    public function boot(Pimple $queue)
    {
        $queue['config.database'] = $this->parseDatabaseOptions($queue['options'],new DbMeta());
        $queue['config.queue']    = $this->parseQueueOptions($queue['options'],new Queue());
        $queue['config.worker']   = $this->parseWorkerOptions($queue['options'],new Worker());
        
        return $queue;
    }
    
}
/* End of File */