<?php
namespace LaterJob\Command;

use Symfony\Component\Console\Helper\Helper;
use LaterJob\Queue;

/**
  *  Injects a Queue API into console commands
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class QueueHelper extends Helper
{
    /**
      *  @var LaterJob\Queue; 
      */
    protected $queue;
    
    
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }
    
    /**
      *  Return the assigned queue
      *
      *  @access public
      *  @return LaterJob\Queue;
      */
    public function getQueue()
    {
        return $this->queue;
    }
    
    
    /**
     * @see Helper
     */
    public function getName()
    {
        return 'queue';
    }
    
}
/* End of File */