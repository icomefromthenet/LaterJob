<?php
namespace LaterJob\Model\Activity;

use LaterJob\Event\QueueEventsMap;
use LaterJob\Event\QueuePurgeActivityEvent;
use LaterJob\Model\Activity\TransitionGateway;
use LaterJob\Model\Activity\Transition;
use LaterJob\Exception as LaterJobException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DBALGateway\Exception as DBALGatewayException;
use DBALGateway\Table\AbstractTable;
use Doctrine\Common\Collections\Collection;


/**
  *  Handle events found in \LaterJob\Event\QueueEventsMap.
  *
  *  Only the QUEUE_PURGE_HISTORY affects transitions directly.
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
            QueueEventsMap::QUEUE_PURGE_HISTORY   => array('onPurgeHistory'),
        );
    }
    
   
    public function onPurgeHistory(QueuePurgeActivityEvent $event)
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
    
}
/* End of File */