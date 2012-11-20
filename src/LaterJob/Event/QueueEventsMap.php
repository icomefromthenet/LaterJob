<?php
namespace LaterJob\Event;

/**
  *  Map of all events that Occur on the Queue in general.
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
final class QueueEventsMap
{
    /**
     * The queue.receive event is queue receives a job 
     *
     * The event listener receives LaterJob\Event\QueueReceiveEvent
     * instance.
     *
     * @var string
     */
   const QUEUE_REC    = 'laterjob.queue.receive';
   
    /**
     * The queue.sent event is used when a job is sent out of the queue
     * for processing. 
     *
     * The event listener receives LaterJob\Event\QueueSendEvent
     * instance.
     *
     * @var string
     */
   const QUEUE_SENT     = 'laterjob.queue.sent';
   
    /**
     * The queue.list event is used when looking for a list of
     * waiting jobs
     *
     * The event listener receives LaterJob\Event\QueueListEvent
     * instance.
     *
     * @var string
     */
   const QUEUE_LIST     = 'laterjob.queue.list';
   
   
   /**
     * The queue.remove event is used when remove operation is run
     * on the queue. A removed job occurs when a job is taken off
     * the queue before it is completed.
     *
     * The event listener receives LaterJob\Event\QueueRemoveEvent
     * instance.
     *
     * @var string
     */
   const QUEUE_REMOVE = 'laterjob.queue.remove';
   
   /**
     * The queue.purge event is used when remove operation is run
     * on the queue. This event occurs when bulk removal of jobs.
     * which are completed or have failed.
     *
     * The event listener receives LaterJob\Event\QueuePurgeEvent
     * instance.
     *
     * @var string
     */
   
   const QUEUE_PURGE  = 'laterjob.queue.purge';
   
   /**
     * The queue.lock event is used when lock operation is run on
     * the queue.
     *
     * The event listener receives LaterJob\Event\QueueLockEvent
     * instance.
     *
     * @var string
     */
   
   const QUEUE_LOCK   = 'laterjob.queue.lock'; 
   
   
   /**
     * The queue.unlock event is used when an unlock operation is
     * run on the queue
     *
     * The event listener receives LaterJob\Event\QueueLockEvent
     * instance.
     *
     * @var string
     */
   
   const QUEUE_UNLOCK = 'laterjob.queue.unlock';
   
   
   
   /**
     * The queue.purge.history event is used to old remove transition history. 
     *
     * The event listener receives LaterJob\Event\QueuePurgeHistoryEvent
     * instance.
     *
     * @var string
     */
   
   const QUEUE_PURGE_HISTORY = 'laterjob.queue.purge.history';
   
    
}
/* End of File */