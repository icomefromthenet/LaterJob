<?php
namespace LaterJob\Log;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use LaterJob\Event\JobEventsMap,
    LaterJob\Event\WorkerEventsMap,
    LaterJob\Event\MonitoringEventsMap,
    LaterJob\Event\QueueEventsMap,
    LaterJob\Event\JobTransitionEvent,
    LaterJob\Event\WorkerTransitionEvent,
    LaterJob\Event\QueueListEvent,
    LaterJob\Event\QueueLockEvent,    
    LaterJob\Event\QueueLookupEvent,
    LaterJob\Event\QueuePurgeEvent,
    LaterJob\Event\QueueQueryActivityEvent,
    LaterJob\Event\QueuePurgeActivityEvent,
    LaterJob\Event\QueueReceiveEvent,
    LaterJob\Event\QueueRemoveEvent,
    LaterJob\Event\QueueSendEvent,
    LaterJob\Event\MonitoringQueryEvent,
    LaterJob\Event\MonitoringEvent,
    LaterJob\Exception as LaterJobException;
 
use Psr\Log\LoggerInterface;
 
/**
  *  Base 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class LogSubscriber implements EventSubscriberInterface
{
    /**
      *  @var LoggerInterface
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
            
            QueueEventsMap::QUEUE_LIST     => array('logQueueListEvent'),
            QueueEventsMap::QUEUE_LOCK     => array('logQueueLockEvent'),
            QueueEventsMap::QUEUE_UNLOCK   => array('logQueueUnlockEvent'),
            
            QueueEventsMap::QUEUE_LOOKUP   => array('logQueueLookupEvent'),
            QueueEventsMap::QUEUE_PURGE    => array('logQueuePurgeEvent'),
            QueueEventsMap::QUEUE_PURGE_ACTIVITY => array('logQueuePurgeActivityEvent'),
            QueueEventsMap::QUEUE_QUERY_ACTIVITY => array('logQueueQueryActivityEvent'),
            
            QueueEventsMap::QUEUE_REC      => array('logQueueReceiveEvent'),
            QueueEventsMap::QUEUE_REMOVE   => array('logQueueRemoveEvent'),
            QueueEventsMap::QUEUE_SENT     => array('logQueueSendEvent'),
            
            
            MonitoringEventsMap::MONITOR_COMMIT => array('logMonitorCommitEvent'),
            MonitoringEventsMap::MONITOR_LOCK   => array('logMonitorLockEvent'),
            MonitoringEventsMap::MONITOR_QUERY  => array('logMonitorQueryEvent'),
            MonitoringEventsMap::MONITOR_RUN    => array('logMonitorRunEvent')
        );
    }
    
    /**
      *  Class Constructor
      *
      *  @access public
      *  @param LoggerInterface $log
      */
    public function __construct(LoggerInterface $log)
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
        $this->log->info($event->getTransition()->getMessage(),array(
                         'job_id' => $event->getJob()->getId()
                        ));
    }
    
    /**
      *  Log events that occur from a worker
      *
      *  @access public
      *  @param WorkerTransitionEvent$event 
      */
    public function logWorkerEvent(WorkerTransitionEvent $event)
    {
        $this->log->info($event->getTransition()->getMessage(),array(
                    'worker_id' => $event->getWorker()->getId()                                                 
                    ));
    }
    
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueListEvent $event 
      */
    public function logQueueListEvent(QueueListEvent $event)
    {
        $this->log->info('QueueListEvent:: Running Queue List ',array(
                        'limit'  => $event->getLimit(),
                        'offset' => $event->getOffset(),
                        'before' => $event->getBefore(),
                        'after'  => $event->getAfter(),
                        'order'  => $event->getOrder(),
                        'state'  => $event->getState()
                        ));
    }
    
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueLockEvent $event 
      */
    public function logQueueLockEvent(QueueLockEvent $event)
    {
        $this->log->info('QueueLockEvent:: Locking Jobs with params',array(
                         'handle'   => $event->getLimit(),
                         'limit'    => $event->getLimit(),
                         'timeout'  => $event->getTimeout(),
                         'now'      => $event->getNow()
                    ));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueLockEvent $event 
      */
    public function logQueueUnlockEvent(QueueLockEvent $event)
    {
        $this->log->info('QueueUnlockEvent:: Unlocking Jobs with params',array(
                         'handle'   => $event->getLimit(),
                         'limit'    => $event->getLimit(),
        ));
    }
    
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueLookupEvent $event 
      */
    public function logQueueLookupEvent(QueueLookupEvent $event)
    {
        $this->log->info('QueueLookupEvent:: Looking up Job with params',array(
                'job_id' => $event->getJobId()   
        ));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueuePurgeEvent $event 
      */
    public function logQueuePurgeEvent(QueuePurgeEvent $event)
    {
        $this->log->info('QueuePurgeEvent:: Purge jobs from queue using params',array(
             'before' => $event->getBeforeDate()   
        ));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueuePurgeActivityEvent $event 
      */
    public function logQueuePurgeActivityEvent(QueuePurgeActivityEvent $event)
    {
        $this->log->info('QueuePurgeEvent:: Purge activity record using params',array(
             'before' => $event->getBeforeDate()   
        ));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueQueryActivityEvent $event 
      */
    public function logQueueQueryActivityEvent(QueueQueryActivityEvent $event)
    {
        $this->log->info('QueueQueryActivityEvent:: Running activity query using params',array(
            'offset' => $event->getOffset(),
            'limit'  => $event->getLimit(),
            'order'  => $event->getOrder(),
            'before' => $event->getBefore(),
            'after'  => $event->getAfter(),
            'job_id' => $event->getJobID(),
            'worker_id' => $event->getWorkerID()
        ));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueReceiveEvent $event 
      */
    public function logQueueReceiveEvent(QueueReceiveEvent $event)
    {
        $this->log->info('QueueReceiveEvent:: Queue received job',array(
             'job_id' => $event->getStorage()->getJobId()
        ));
    }
    
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueRemoveEvent $event 
      */
    public function logQueueRemoveEvent(QueueRemoveEvent $event)
    {
        $this->log->info('QueueRemoveEvent:: Removing job from Queue ',array(
             'job_id' => $event->getJobId(),
             'now'    => $event->getNow()
        )); 
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueSendEvent $event 
      */
    public function logQueueSendEvent(QueueSendEvent $event)
    {
        $this->log->info('QueueSendEvent:: Sending jobs to worker',array(
             'handle'  => $event->getHandle(),
             'limit'   => $event->getLimit(),
             'timeout' => $event->getTimeout()
        ));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param MonitoringEvent $event 
      */
    public function logMonitorCommitEvent(MonitoringEvent $event)
    {
        $this->log->info('MonitoringEvent:: Monitor committing data record for',array(
             'date' => $event->getStats()->getMonitorDate()
        ));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param MonitoringEvent $event 
      */
    public function logMonitorLockEvent(MonitoringEvent $event)
    {
        $this->log->info('MonitoringEvent:: Monitor locking record for',array(
             'date' => $event->getStats()->getMonitorDate()
        ));
    }
    
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param MonitoringEvent $event 
      */
    public function logMonitorRunEvent(MonitoringEvent $event)
    {
        $this->log->info('MonitoringEvent:: Monitor running monitor for date',array(
             'date' => $event->getStats()->getMonitorDate()
        ));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param MonitoringQueryEvent $event 
      */
    public function logMonitorQueryEvent(MonitoringQueryEvent $event)
    {
        $this->log->info('MonitoringEvent:: Monitor running query with params',array(
             'limit'  => $event->getLimit(),
             'offset' => $event->getOffset(),
             'order'  => $event->getOrder(),
             'start'  => $event->getStart(),
             'end'    => $event->getEnd(),
             'include_calculating' => $event->getIncludeCalculating()
        ));
    }
    
}
/* End of File */