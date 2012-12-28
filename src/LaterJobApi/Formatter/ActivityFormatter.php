<?php
namespace LaterJobApi\Formatter;

use LaterJob\Model\Activity\Transition;
use Doctrine\Common\Collections\Collection;

class ActivityFormatter
{
    public function toArray(Transition $entity)
    {
        return array(
            'transitionId'      => $entity->getTransitionId(),
            'dateOccured'       => $entity->getOccured(), 
            'stateId'           => $entity->getState(),
            'workerId'          => $entity->getWorker(),
            'jobId'             => $entity->getJob(),
            'transitionMessage' => $entity->getMessage(),
            'processHandle'     => $entity->getProcessHandle()
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