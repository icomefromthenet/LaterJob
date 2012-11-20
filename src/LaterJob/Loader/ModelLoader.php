<?php
namespace LaterJob\Loader;

use Pimple;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use LaterJob\Model\Transition\TransitionBuilder;
use LaterJob\Model\Transition\TransitionGateway;
use LaterJob\Model\Queue\StorageBuilder;
use LaterJob\Model\Queue\StorageGateway;
use LaterJob\Model\Monitor\StatsBuilder;
use LaterJob\Model\Monitor\StatsGateway;

/**
  *  Loads the models used.
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class ModelLoader implements LoaderInterface
{
    
    public function bootTransitionModel($table, Connection $db, EventDispatcherInterface $event, $meta)
    {
        return new TransitionGateway($table,$db,$event,$meta,null, new TransitionBuilder());
    }
    
    
    public function bootStorageModel($table, Connection $db, EventDispatcherInterface $event, $meta)
    {
        return new StorageGateway($table,$db,$event,$meta,null,new StorageBuilder());
    }

    
    public function bootMonitorModel($table, Connection $db, EventDispatcherInterface $event, $meta)
    {
        return new StatsGateway($table,$db,$event,$meta,null, new StatsBuilder());
    }
    
    public function boot(Pimple $queue)
    {
        $doctrine = $queue['doctrine'];
        $event    = $queue['dispatcher'];
        
        $queue['model.transition'] = $this->bootTransitionModel($queue['config.database']->getTransitionTableName(),
                                                                   $doctrine,
                                                                   $event,
                                                                   $queue['config.database']->getTransitionTable()
                                                                   );
        

        $queue['model.queue'] = $this->bootStorageModel($queue['config.database']->getQueueTableName(),
                                                                   $doctrine,
                                                                   $event,
                                                                   $queue['config.database']->getQueueTable()
                                                                   );
        
        $queue['model.monitor'] = $this->bootMonitorModel($queue['config.database']->getMonitorTableName(),
                                                                   $doctrine,
                                                                   $event,
                                                                   $queue['config.database']->getMonitorTable()
                                                                   );
        
        return $queue;        
    }
}

/* End of File */