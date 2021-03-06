<?php
namespace LaterJob\Loader;

use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DBALGateway\Table\AbstractTable;
use Psr\Log\LoggerInterface;
use LaterJob\Log\LogSubscriber;
use LaterJob\Model\Queue\QueueSubscriber; 
use LaterJob\Model\Queue\JobSubscriber;
use LaterJob\Model\Activity\JobSubscriber    as TransitionJobSubscriber;
use LaterJob\Model\Activity\WorkerSubscriber as TransitionWorkerSubscriber;
use LaterJob\Model\Activity\QueueSubscriber  as TransitionQueueSubscriber;
use LaterJob\Model\Monitor\MonitorSubscriber as MonitorSubscriber;

/**
  *  Loads the and subscribes the event handlers.
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class EventSubscriber implements LoaderInterface
{
    
    
    public function subscribeLogHandlers(EventDispatcherInterface $event,LoggerInterface $log)
    {
        $log_subscriber = new LogSubscriber($log);
        $event->addSubscriber($log_subscriber);
    }
    
    
    public function subscribeStorageHandlers(EventDispatcherInterface $event,AbstractTable $storage_gateway)
    {
        $queue_subscriber = new QueueSubscriber($storage_gateway);
        $job_subscriber   = new JobSubscriber($storage_gateway);
        
        $event->addSubscriber($queue_subscriber);
        $event->addSubscriber($job_subscriber);
    }
    
    public function subscribeTransitionHandlers(EventDispatcherInterface $event,AbstractTable $transition_gateway)
    {
        $job_subscriber    = new TransitionJobSubscriber($transition_gateway);
        $worker_subscriber = new TransitionWorkerSubscriber($transition_gateway);
        $queue_subscriber  = new TransitionQueueSubscriber($transition_gateway);
        
        $event->addSubscriber($job_subscriber);
        $event->addSubscriber($worker_subscriber);
        $event->addSubscriber($queue_subscriber);
    }
    
    public function subscribeMonitorHandlers(EventDispatcherInterface $event,AbstractTable $monitor_gateway,AbstractTable $transition_gateway)
    {
        $monitor_sub  = new MonitorSubscriber($monitor_gateway,$transition_gateway);
        
        $event->addSubscriber($monitor_sub);
    }
    
    public function boot(Container $queue)
    {
        $event = $queue['dispatcher'];
        
        $this->subscribeLogHandlers($event,$queue['logger']);
        $this->subscribeStorageHandlers($event,$queue['model.queue']);
        $this->subscribeTransitionHandlers($event,$queue['model.transition']);
        $this->subscribeMonitorHandlers($event,$queue['model.monitor'],$queue['model.transition']);
        
        return $queue;
    }
}

/* End of File */