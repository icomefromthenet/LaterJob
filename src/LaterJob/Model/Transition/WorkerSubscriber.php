<?php
namespace LaterJob\Model\Transition;

use LaterJob\Event\WorkerEventsMap;
use LaterJob\Event\WorkerTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DBALGateway\Exception as DBALGatewayException;
use DBALGateway\Table\AbstractTable;
use LaterJob\Exception as LaterJobException;

/**
  *  Handle events found in \LaterJob\Event\WorkEventsMap 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class WorkerSubscriber implements EventSubscriberInterface
{
    /**
      *  @var TransitionGateway 
      */
    protected $gateway;

    
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
            WorkerEventsMap::WORKER_START  => array('onWorkerStart'),
            WorkerEventsMap::WORKER_FINISH => array('onWorkerFinish'),
            WorkerEventsMap::WORKER_ERROR  => array('onWorkerError'),
        );
    }
    
    /**
      *  Class Constructor
      *
      *  @access public
      *  @param AbstractTable $gateway
      */
    public function __construct(AbstractTable $gateway)
    {
        $this->gateway = $gateway;
    }
    
    
    /**
      *  Handles the event WorkerEventsMap::WORKER_ERROR
      *
      *  @access public
      *  @return void
      *  @param WorkerTransitionEvent $event
      */
    public function onWorkerError(WorkerTransitionEvent $event)
    {
        $this->saveTransition($event);
    }
    
    /**
      *  Handles the event WorkerEventsMap::WORKER_FINISH
      *
      *  @access public
      *  @return void
      *  @param WorkerTransitionEvent $event
      */
    public function onWorkerFinish(WorkerTransitionEvent $event)
    {
        $this->saveTransition($event);
    }
    
    /**
      *  Handles the event WorkerEventsMap::WORKER_START
      *
      *  @access public
      *  @return void
      *  @param WorkerTransitionEvent $event
      */
    public function onWorkerStart(WorkerTransitionEvent $event)
    {
        $this->saveTransition($event);
    }
    
    /**
      *  Save the transition to the database
      *
      *  @access public
      *  @return boolean
      *  @param WorkerTransitionEvent $event
      */
    protected function saveTransition(WorkerTransitionEvent $event)
    {
        try {

            $obj = $event->getTransition();
            $result = $this->gateway->insertQuery()
                            ->start()
                                ->addColumn('worker_id',$obj->getWorker())
                                ->addColumn('state_id',$obj->getState())
                                ->addColumn('dte_occured',$obj->getOccured())
                                ->addColumn('transition_msg',$obj->getMessage())
                                ->addColumn('process_handle',$obj->getProcessHandle())
                            ->end()
                        ->insert();
            
            # assign the id to the object
            $obj->setTransitionId($this->gateway->lastInsertId());
            
            if($result === false) {
                throw new LaterJobException('Unable to save transition for Worker:: '.$obj->getWorker() .' It already exists');    
            }
            
        }catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
    }
    
}
/* End of File */