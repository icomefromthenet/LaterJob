<?php
namespace LaterJob\Model\Queue;

use LaterJob\Model\Queue\Storage;
use DBALGateway\Builder\BuilderInterface;
use DateTime;

/**
  *  Builder for Queue Job Entities
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class StorageBuilder implements BuilderInterface
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
        $object = new Storage();
        
        $object->setJobId($data['job_id']);
        $object->setRetryLeft($data['retry_count']);
        $object->setDateAdded($data['dte_add']);
        $object->setState($data['state_id']);
        $object->setJobData($data['job_data']);
        
        if($data['lock_timeout'] instanceof DateTime) {
            $object->setLockoutTimer($data['lock_timeout']);    
        }
        
        if($data['retry_last'] instanceof DateTime) {
            $object->setRetryLast($data['lock_timeout']);    
        }
        
        if($data['handle'] !== null) {
            $object->setLockoutHandle($data['handle']);    
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
           'job_id'      => $entity->getJobId(),
           'retry_count' => $entity->getRetryLeft(),
           'dte_add'     => $entity->getDateAdded(),
           'state_id'    => $entity->getState(),
           'job_data'    => $entity->getJobData(),
           'lock_timeout'=> $entity->getLockoutTimer(),
           'handle'      => $entity->getLockoutHandle(),
           'retry_last'  => $entity->getRetryLast()
        );
        
    }
    
}

/* End of File */