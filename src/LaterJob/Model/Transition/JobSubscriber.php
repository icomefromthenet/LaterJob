<?php
namespace LaterJob\Model\Transition;

use LaterJob\Event\JobEventsMap;
use LaterJob\Event\JobTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DBALGateway\Exception as DBALGatewayException;
use DBALGateway\Table\AbstractTable;
use LaterJob\Exception as LaterJobException;

/**
  *  Handle events found in \LaterJob\Event\JobEventsMap 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class JobSubscriber implements EventSubscriberInterface
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
            JobEventsMap::STATE_START  => array('onJobStart'),
            JobEventsMap::STATE_FAIL   => array('onJobFail'),
            JobEventsMap::STATE_ERROR  => array('onJobError'),
            JobEventsMap::STATE_ADD    => array('onJobAdd'),
            JobEventsMap::STATE_FINISH => array('onJobFinish'),
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
      *  Handles the event JobEventsMap::STATE_ERROR
      *
      *  @access public
      *  @return void
      *  @param JobTransitionEvent $event
      */
    public function onJobError(JobTransitionEvent $event)
    {
        $this->saveTransition($event);
    }
    
    /**
      *  Handles the event JobEventsMap::STATE_FINISH
      *
      *  @access public
      *  @return void
      *  @param JobTransitionEvent $event
      */
    public function onJobFinish(JobTransitionEvent $event)
    {
        $this->saveTransition($event);
    }
    
    /**
      *  Handles the event JobEventsMap::STATE_START
      *
      *  @access public
      *  @return void
      *  @param JobTransitionEvent $event
      */
    public function onJobStart(JobTransitionEvent $event)
    {
        $this->saveTransition($event);
    }
    
    /**
      *  Handles the event JobEventsMap::STATE_FAIL
      *
      *  @access public
      *  @return void
      *  @param JobTransitionEvent $event
      */
    public function onJobFail(JobTransitionEvent $event)
    {
        $this->saveTransition($event);
    }
    
     /**
      *  Handles the event JobEventsMap::STATE_ADD
      *
      *  @access public
      *  @return void
      *  @param JobTransitionEvent $event
      */
    public function onJobAdd(JobTransitionEvent $event)
    {
        $this->saveTransition($event);
    }
    
    /**
      *  Save the transition to the database
      *
      *  @access public
      *  @return boolean
      *  @param JobTransitionEvent $event
      */
    protected function saveTransition(JobTransitionEvent $event)
    {
        try {
            $obj = $event->getTransition();
            $result = $this->gateway->insertQuery()
                            ->start()
                                ->addColumn('job_id',$obj->getJob())
                                ->addColumn('state_id',$obj->getState())
                                ->addColumn('dte_occured',$obj->getOccured())
                                ->addColumn('transition_msg',$obj->getMessage())
                            ->end()
                        ->insert();
            
            # assign the id to the object
            $obj->setTransitionId($this->gateway->lastInsertId());
            
            if($result === false) {
                throw new LaterJobException('Unable to save transition for Job:: '.$obj->getJob() .' It already exists');    
            }
            
        } catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
    }
    
}
/* End of File */