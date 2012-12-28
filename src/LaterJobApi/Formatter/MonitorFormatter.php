<?php
namespace LaterJobApi\Formatter;

use LaterJob\Model\Monitor\Stats;
use Doctrine\Common\Collections\Collection;

class MonitorFormatter
{
    public function toArray(Stats $entity)
    {
        return array(
           'monitorId'                => $entity->getMonitorId(),
           'monitorComplete'          => $entity->getComplete(),
           'monitorDate'              => $entity->getMonitorDate(),
           'workerMaxTime'            => $entity->getWorkerMaxTime(),
           'workerMinTime'            => $entity->getWorkerMinTime(),
           'workerMeanTime'           => $entity->getWorkerMeanTime(),
           'workerMaxThroughput'      => $entity->getWorkerMaxThroughput(),
           'workerMeanThroughput'     => $entity->getWorkerMeanThroughput(),
           'workerMeanUtilization'    => $entity->getWorkerMeanUtilization(),
           'queueNumberAddedJobs'     => $entity->getQueueJobsAdded(),
           'queueNumberFinishedJobs'  => $entity->getQueueJobsCompleted(),
           'queueNumberErrorJobs'     => $entity->getQueueJobsError(),
           'queueNumberFailedJobs'    => $entity->getQueueJobsFailed(),
           'queueNumberStartedJobs'   => $entity->getQueueJobsProcessing(),
           'queueMaxServiceTime'      => $entity->getJobMaxServiceTime(),
           'queueMinServiceTime'      => $entity->getJobMinServiceTime(),
           'queueMeanServiceTime'     => $entity->getJobMeanServiceTime()
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