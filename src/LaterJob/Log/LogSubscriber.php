<?php
namespace LaterJob\Log;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use LaterJob\Event\JobEventsMap;
use LaterJob\Event\JobTransitionEvent;
use LaterJob\Event\WorkerEventsMap;
use LaterJob\Event\WorkerTransitionEvent;
use LaterJob\Exception as LaterJobException;

/**
  *  Base 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class LogSubscriber implements EventSubscriberInterface
{
    /**
      *  @var LogInterface
      */
    protected $log;

    
    /**
      *  Bind event handlers to the dispatcher
      *
      *  @access public
      *  @static 
      *  @return array a binding to event handlers
      */
    static public function getSubscribedEvents()
    {
        return array(
            JobEventsMap::STATE_START      => array('logJobEvent'),
            JobEventsMap::STATE_FAIL       => array('logJobEvent'),
            JobEventsMap::STATE_ERROR      => array('logJobEvent'),
            JobEventsMap::STATE_ADD        => array('logJobEvent'),
            JobEventsMap::STATE_FINISH     => array('logJobEvent'),
            WorkerEventsMap::WORKER_START  => array('logWorkerEvent'),
            WorkerEventsMap::WORKER_FINISH => array('logWorkerEvent'),
            WorkerEventsMap::WORKER_ERROR  => array('logWorkerEvent'),
        );
    }
    
    /**
      *  Class Constructor
      *
      *  @access public
      *  @param LogInterface $log
      */
    public function __construct(LogInterface $log)
    {
        $this->log = $log;
    }
    
    //------------------------------------------------------------------
    # Log Handlers
    
    /**
      *  Log events that occur from job
      *
      *  @access public
      *  @param JobTransitionEvent $event 
      */
    public function logJobEvent(JobTransitionEvent $event)
    {
        $this->log->info($event->getTransition()->getMessage());
    }
    
    /**
      *  Log events that occur from a worker
      *
      *  @access public
      *  @param workerTransitionEvent $event 
      */
    public function logWorkerEvent(WorkerTransitionEvent $event)
    {
        $this->log->info($event->getTransition()->getMessage());
    }
    
}
/* End of File */