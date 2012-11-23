<?php
namespace LaterJob;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use LaterJob\Event\QueueEventsMap;
use LaterJob\Event\QueuePurgeActivityEvent;
use LaterJob\Event\QueueQueryActivityEvent;
use DateTime;
use Traversable;

/**
  *  Activity API used to access the transiton history of jobs and workers
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class Activity
{
    
    /**
      *  @var EventDispatcherInterface 
      */
    protected $event;
    
    /**
      *  Class Constructor
      *
      *  @access public
      *  @return void
      *  @param Symfony\Component\EventDispatcher\EventDispatcherInterface $event
      */
    public function __construct(EventDispatcherInterface $event)
    {
        $this->event = $event;
    }
    
    /**
      *  Query the queue activity (transitions)
      *
      *  @access public
      *  @param integer $offset
      *  @param integer $limit
      *  @param string $order 'ASC | DESC'
      *  @param DateTime $before
      *  @param DateTime $after
      *  @return Traversable 
      */
    public function query($offset, $limit, $order = 'ASC', DateTime $before = null, DateTime $after = null)
    {
        $event = new QueueQueryActivityEvent($offset,$limit,$order,$before,$after);
        
        $this->event->dispatch(QueueEventsMap::QUEUE_QUERY_ACTIVITY,$event);
        
        return $event->getResult();
    }
    
    /**
      *  Purge all activity before date x
      *
      *  @access public
      *  @return integer amount of activities purged
      */
    public function purge(DateTime $before)
    {
        $event = new QueuePurgeActivityEvent($before);
        
        $this->event->dispatch(QueueEventsMap::QUEUE_PURGE_ACTIVITY,$event);
        
        return $event->getResult();
    }
    
    
}
/* End of File */
