<?php
namespace LaterJobApi\Formatter;

use LaterJob\Model\Queue\Storage;
use Doctrine\Common\Collections\Collection;

class JobFormatter
{
    public function toArray(Storage $entity)
    {
        return array(
           'jobId'       => $entity->getJobId(),
           'retryCount'  => $entity->getRetryLeft(),
           'dateAdded'   => $entity->getDateAdded(),
           'stateId'     => $entity->getState(),
           'jobData'     => $entity->getJobData(),
           'lockTimeout' => $entity->getLockoutTimer(),
           'handle'      => $entity->getLockoutHandle(),
           'retryLast'   => $entity->getRetryLast()
        );
    }
    
    public function toArrayCollection(Collection $col)
    {
        $results = array();
        
        foreach($col as $item) {
            $results[] = $this->toArray($item);
        }
        
        return $results;
    }
   
}
/* End of File */