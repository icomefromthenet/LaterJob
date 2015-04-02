<?php
namespace LaterJob\Model\Activity;

use LaterJob\Event\QueueEventsMap;
use LaterJob\Event\QueuePurgeActivityEvent;
use LaterJob\Event\QueueQueryActivityEvent;
use LaterJob\Model\Activity\TransitionGateway;
use LaterJob\Model\Activity\Transition;
use LaterJob\Exception as LaterJobException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DBALGateway\Exception as DBALGatewayException;
use DBALGateway\Table\AbstractTable;
use Doctrine\Common\Collections\Collection;
use DateTime;


/**
  *  Handle events found in \LaterJob\Event\QueueEventsMap.
  *
  *  Only the QUEUE_PURGE_ACTIVITY affects transitions directly.
  *  
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueueSubscriber implements EventSubscriberInterface
{
    /**
      *  @var TransitionGateway 
      */
    protected $gateway;

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
      *  Bind event handlers to the dispatcher
      *
      *  @access public
      *  @static 
      *  @return array a binding to event handlers
      */
    static public function getSubscribedEvents()
    {
        return array(
            QueueEventsMap::QUEUE_PURGE_ACTIVITY   => array('onPurgeActivity'),
            QueueEventsMap::QUEUE_QUERY_ACTIVITY   => array('onQueryActivity'),
        );
    }
    
   
    public function onPurgeActivity(QueuePurgeActivityEvent $event)
    {
         try {
            
            # purge jobs that have been completed or failed
            # before x date 
            $result = $this->gateway->deleteQuery()
                        ->start()
                            ->filterOccuredBefore($event->getBeforeDate())
                        ->end()
                    ->delete();
                        
            $event->setResult($this->gateway->rowsAffected());
        
        } catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
    }
    
    
    public function onQueryActivity(QueueQueryActivityEvent $event)
    {
         try {
            
            $query = $this->gateway->selectQuery()->start();
            
            if($event->getAfter() instanceof DateTime) {
                $query->filterOccuredAfter($event->getAfter());
            }
            
            if($event->getBefore() instanceof DateTime) {
                $query->filterOccuredBefore($event->getBefore());
            }
            
            if($event->getLimit() !== null) {
                $query->limit($event->getLimit());
            }
            
            if($event->getOffset() !== null) {
                $query->offset($event->getOffset());
            }
            
            if($event->getWorkerID() !== null) {
                $query->filterByWorker($event->getWorkerID());    
            }   
            
            if($event->getJobID() !== null) {
                $query->filterByJob($event->getJobID());
            }
            
            # set the order by the date added
            $query->orderByOccured($event->getOrder());
            
            $event->setResult($query->end()->find());
        
        } catch(DBALGatewayException $e) {
            throw new LaterJobException($e->getMessage(),0,$e);
        }
        
    }
    
    
}
/* End of File */