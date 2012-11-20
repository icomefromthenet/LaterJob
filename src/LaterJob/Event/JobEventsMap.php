<?php
namespace LaterJob\Event;

/**
  *  Map of all events that Job instance emits 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
final class JobEventsMap
{
    /**
     * The job.added event is thrown each time a job is added to a queue
     *
     * The event listener receives LaterJob\Event\JobTransitionEvent
     * instance.
     *
     * @var string
     */
   const STATE_ADD   = 'laterjob.job.add';
   
    /**
     * The job.started event is thrown each time a job processing has started
     *
     * The event listener receives LaterJob\Event\JobTransitionEvent
     * instance.
     *
     * @var string
     */
   const STATE_START = 'laterjob.job.start';
   
    /**
     * The job.finish event is thrown each time a job finished processing
     *
     * The event listener receives LaterJob\Event\JobTransitionEvent
     * instance.
     *
     * @var string
     */
   const STATE_FINISH  = 'laterjob.job.finished';
   
    /**
     * The job.error event is thrown each time an error is reached
     *
     * The event listener receives LaterJob\Event\JobTransitionEvent
     * instance.
     *
     * @var string
     */
   const STATE_ERROR   = 'laterjob.job.error';
    
    /**
     * The job.fail event is thrown after job exceeds its maxium retry count
     *
     * The event listener receives LaterJob\Event\JobTransitionEvent
     * instance
     *
     * @var string
     */
   const STATE_FAIL  = 'laterjob.job.fail';
    
}
/* End of File */