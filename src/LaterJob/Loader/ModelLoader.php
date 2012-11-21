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
    /**
      *  @var Doctrine\DBAL\Connection 
      */
    protected $doctrine;
    
    
    public function bootTransitionModel($table, EventDispatcherInterface $event, $meta)
    {
        return new TransitionGateway($table,$this->doctrine,$event,$meta,null, new TransitionBuilder());
    }
    
    
    public function bootStorageModel($table, EventDispatcherInterface $event, $meta)
    {
        return new StorageGateway($table,$this->doctrine,$event,$meta,null,new StorageBuilder());
    }

    
    public function bootMonitorModel($table, EventDispatcherInterface $event, $meta)
    {
        return new StatsGateway($table,$this->doctrine,$event,$meta,null, new StatsBuilder());
    }
    
    public function boot(Pimple $queue)
    {
        # assign doctrine to the DI object
        $queue['doctrine'] = $this->doctrine;
        
        # fetch reference to event handler for quicker lookup
        $event = $queue['dispatcher'];
        
        $queue['model.transition'] = $this->bootTransitionModel($queue['config.database']->getTransitionTableName(),
                                                                   $event,
                                                                   $queue['config.database']->getTransitionTable()
                                                                   );
        

        $queue['model.queue'] = $this->bootStorageModel($queue['config.database']->getQueueTableName(),
                                                                   $event,
                                                                   $queue['config.database']->getQueueTable()
                                                                   );
        
        $queue['model.monitor'] = $this->bootMonitorModel($queue['config.database']->getMonitorTableName(),
                                                                   $event,
                                                                   $queue['config.database']->getMonitorTable()
                                                                   );
        
        return $queue;        
    }
    
    /**
      *  Class Constructor
      *
      *  @param
      */
    public function __construct(Connection $doctrine)
    {
        $this->doctrine = $doctrine;
    }
}
/* End of File */