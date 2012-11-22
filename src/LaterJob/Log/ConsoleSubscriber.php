<?php
namespace LaterJob\Log;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use LaterJob\Event\JobEventsMap;
use LaterJob\Event\WorkerEventsMap;
use LaterJob\Event\JobTransitionEvent;
use LaterJob\Event\WorkerTransitionEvent;


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
        );
    }
    
    
      /**
      *  Log events that occur from job
      *
      *  @access public
      *  @param JobTransitionEvent $event 
      */
    public function logJobEvent(JobTransitionEvent $event)
    {
        $this->output->writeln($event->getTransition()->getMessage());
    }
    
    /**
      *  Log events that occur from a worker
      *
      *  @access public
      *  @param workerTransitionEvent $event 
      */
    public function logWorkerEvent(WorkerTransitionEvent $event)
    {
        $this->output->writeln('<comment>'.$event->getTransition()->getMessage(). '</comment>');
    }
}
/* End of File */