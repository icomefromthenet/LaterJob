<?php
namespace LaterJob\Event;

/**
  *  Map of all events that a Worker will emit 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
final class WorkerEventsMap
{
    
    /**
     * The worker.start event is emitted when work starts to execute.
     *
     * The event listener receives an Acme\StoreBundle\Event\FilterOrderEvent
     * instance.
     *
     * @var string
     */
    const WORKER_START = 'laterjob.worker.start';
    
    
    /**
     * The worker.finish event is emitted when stop executing
     *
     * The event listener receives an Acme\StoreBundle\Event\FilterOrderEvent
     * instance.
     *
     * @var string
     */
    const WORKER_FINISH = 'laterjob.worker.finish';
    
    /**
     * The worker.errorevent is emitted when stop executing due to error exception
     *
     * The event listener receives an Acme\StoreBundle\Event\FilterOrderEvent
     * instance.
     *
     * @var string
     */
    const WORKER_ERROR = 'laterjob.worker.error';
        
}
/* End of File */