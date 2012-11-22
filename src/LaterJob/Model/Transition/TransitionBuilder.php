<?php
namespace LaterJob\Model\Transition;

use LaterJob\Model\Transition\Transition;
use DBALGateway\Builder\BuilderInterface;

/**
  *  Base 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class TransitionBuilder implements BuilderInterface
{
    
    /**
      *  Convert data array into entity
      *
      *  @return mixed
      *  @param array $data
      *  @access public
      */
    public function build($data)
    {
        $object = new Transition();
        $object->setTransitionId($data['transition_id']);
        $object->setOccured($data['dte_occured']);
        $object->setState($data['state_id']);
        
        
        if(!empty($data['process_handle'])) {
            $object->setProcessHandle($data['process_handle']);        
        }
        
        if(isset($data['transition_msg'])) {
            if(!empty($data['transition_msg'])) {
                $object->setMessage($data['transition_msg']);        
            }
        }
        
        if(isset($data['worker_id'])) {
            if(!empty($data['worker_id'])) {
                $object->setWorker($data['worker_id']);        
            }
        }


        if(isset($data['job_id'])) {
            if(!empty($data['job_id'])) {
                $object->setJob($data['job_id']);        
            }
        }
        
        
        return $object;
    }
    
    /**
      *  Convert and entity into a data array
      *
      *  @return array
      *  @access public
      */
    public function demolish($entity)
    {
        return array(
            'transition_id' => $entity->getTransitionId(),
            'dte_occured'   => $entity->getOccured(), 
            'state_id'      => $entity->getState(),
            'worker_id'     => $entity->getWorker(),
            'job_id'        => $entity->getJob(),
            'transition_msg'=> $entity->getMessage(),
            'process_handle'=> $entity->getProcessHandle()
        );
        
    }
    
}

/* End of File */