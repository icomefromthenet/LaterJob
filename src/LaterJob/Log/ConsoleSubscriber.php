<?php
namespace LaterJob\Log;

use Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\EventDispatcher\EventSubscriberInterface;

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


/**
  *  Record Events for the console 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class ConsoleSubscriber implements EventSubscriberInterface
{
    /**
     *  @var  Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;
   
    /**
     *  Class constructor
     *
     *  Symfony\Component\Console\Output\OutputInterface $out
     */
    public function __construct(OutputInterface $out)
    {
         $this->output = $out;
    }
   
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
    
    
    //------------------------------------------------------------------
    # Handlers
    
    /**
      *  Log events that occur from job
      *
      *  @access public
      *  @param JobTransitionEvent $event 
      */
    public function logJobEvent(JobTransitionEvent $event)
    {
        $this->output->writeln($event->getTransition()->getMessage().' '.json_encode(array(
                         'job_id' => $event->getJob()->getId()
                        )));
    }
    
    /**
      *  Log events that occur from a worker
      *
      *  @access public
      *  @param WorkerTransitionEvent$event 
      */
    public function logWorkerEvent(WorkerTransitionEvent $event)
    {
        $this->output->writeln('<comment>'.$event->getTransition()->getMessage().'</comment> '.json_encode(array('worker_id' => $event->getWorker()->getId())));
    }
    
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueListEvent $event 
      */
    public function logQueueListEvent(QueueListEvent $event)
    {
        $this->output->writeln('QueueListEvent:: Running Queue List '.json_encode(array(
                        'limit'  => $event->getLimit(),
                        'offset' => $event->getOffset(),
                        'before' => $event->getBefore(),
                        'after'  => $event->getAfter(),
                        'order'  => $event->getOrder(),
                        'state'  => $event->getState()
                        )));
    }
    
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueLockEvent $event 
      */
    public function logQueueLockEvent(QueueLockEvent $event)
    {
        $this->output->writeln('QueueLockEvent:: Locking Jobs with params '.json_encode(array(
                         'handle'   => $event->getLimit(),
                         'limit'    => $event->getLimit(),
                         'timeout'  => $event->getTimeout(),
                         'now'      => $event->getNow()
                    )));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueLockEvent $event 
      */
    public function logQueueUnlockEvent(QueueLockEvent $event)
    {
        $this->output->writeln('QueueUnlockEvent:: Unlocking Jobs with params '.json_encode(array(
                         'handle'   => $event->getLimit(),
                         'limit'    => $event->getLimit(),
        )));
    }
    
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueLookupEvent $event 
      */
    public function logQueueLookupEvent(QueueLookupEvent $event)
    {
        $this->output->writeln('QueueLookupEvent:: Looking up Job with params' .json_encode(array(
                'job_id' => $event->getJobId()   
        )));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueuePurgeEvent $event 
      */
    public function logQueuePurgeEvent(QueuePurgeEvent $event)
    {
        $this->output->writeln('QueuePurgeEvent:: Purge jobs from queue using params '.json_encode(array(
             'before' => $event->getBeforeDate()   
        )));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueuePurgeActivityEvent $event 
      */
    public function logQueuePurgeActivityEvent(QueuePurgeActivityEvent $event)
    {
        $this->output->writeln('QueuePurgeEvent:: Purge activity record using params '.json_encode(array(
             'before' => $event->getBeforeDate()   
        )));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueQueryActivityEvent $event 
      */
    public function logQueueQueryActivityEvent(QueueQueryActivityEvent $event)
    {
        $this->output->writeln('QueueQueryActivityEvent:: Running activity query using params '.json_encode(array(
            'offset' => $event->getOffset(),
            'limit'  => $event->getLimit(),
            'order'  => $event->getOrder(),
            'before' => $event->getBefore(),
            'after'  => $event->getAfter(),
            'job_id' => $event->getJobID(),
            'worker_id' => $event->getWorkerID()
        )));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueReceiveEvent $event 
      */
    public function logQueueReceiveEvent(QueueReceiveEvent $event)
    {
        $this->output->writeln('QueueReceiveEvent:: Queue received job '.json_encode(array(
             'job_id' => $event->getStorage()->getJobId()
        )));
    }
    
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueRemoveEvent $event 
      */
    public function logQueueRemoveEvent(QueueRemoveEvent $event)
    {
        $this->output->writeln('QueueRemoveEvent:: Removing job from Queue '.json_encode(array(
             'job_id' => $event->getJobId(),
             'now'    => $event->getNow()
        ))); 
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param QueueSendEvent $event 
      */
    public function logQueueSendEvent(QueueSendEvent $event)
    {
        $this->output->writeln('QueueSendEvent:: Sending jobs to worker '.json_encode(array(
             'handle'  => $event->getHandle(),
             'limit'   => $event->getLimit(),
             'timeout' => $event->getTimeout()
        )));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param MonitoringEvent $event 
      */
    public function logMonitorCommitEvent(MonitoringEvent $event)
    {
        $this->output->writeln('MonitoringEvent:: Monitor committing data record for '.json_encode(array(
             'date' => $event->getStats()->getMonitorDate()
        )));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param MonitoringEvent $event 
      */
    public function logMonitorLockEvent(MonitoringEvent $event)
    {
        $this->output->writeln('MonitoringEvent:: Monitor locking record for '.json_encode(array(
             'date' => $event->getStats()->getMonitorDate()
        )));
    }
    
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param MonitoringEvent $event 
      */
    public function logMonitorRunEvent(MonitoringEvent $event)
    {
        $this->output->writeln('MonitoringEvent:: Monitor running monitor for date '.json_encode(array(
             'date' => $event->getStats()->getMonitorDate()
        )));
    }
    
    /**
      *  Log events that occur from queue
      *
      *  @access public
      *  @param MonitoringQueryEvent $event 
      */
    public function logMonitorQueryEvent(MonitoringQueryEvent $event)
    {
        $this->output->writeln('MonitoringEvent:: Monitor running query with params '.json_encode(array(
             'limit'  => $event->getLimit(),
             'offset' => $event->getOffset(),
             'order'  => $event->getOrder(),
             'start'  => $event->getStart(),
             'end'    => $event->getEnd(),
             'include_calculating' => $event->getIncludeCalculating()
        )));
    }
    
    
}
/* End of File */