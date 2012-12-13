<?php
namespace LaterJob\Model\Activity;

use DBALGateway\Query\AbstractQuery;
use DateTime;
use LaterJob\Config\QueueConfig;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

/**
  *  Query class for Transitions 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class TransitionQuery extends AbstractQuery
{
    
    /**
      *  Filter only incude a particular worker
      *
      *  @access public
      *  @return TransitionQuery
      *  @param string $id of the worker
      */
    public function filterByWorker($id)
    {
        $this->where($this->expr()->eq('worker_id',':worker_id'))->setParameter('worker_id',$id,$this->getGateway()->getMetaData()->getColumn('worker_id')->getType());
        
        return $this;
    }
    
    /**
      *  Filter only incude a particular job
      *
      *  @access public
      *  @return TransitionQuery
      *  @param string $id of the job
      */
    public function filterByJob($id)
    {
        $this->where($this->expr()->eq('job_id',':job_id'))->setParameter('job_id',$id,$this->getGateway()->getMetaData()->getColumn('job_id')->getType());
        
        return $this;
    }
    
    /**
      *  Filter to a single transition row
      *
      *  @access public
      *  @return TransitionQuery
      *  @param integer $id of the transition
      */
    public function filterByTransition($id)
    {
        $this->where($this->expr()->eq('transition_id',':transition_id'))->setParameter('transition_id',$id,$this->getGateway()->getMetaData()->getColumn('transition_id')->getType());
        
        return $this;
    }
    
    
    /**
      *  Filter to group of States
      *
      *  @access public
      *  @return TransitionQuery
      *  @param integer $id of the transition
      */
    public function filterByStates($id)
    {
        $arguments           = func_get_args();
        $processed_arguments = array();
        
        foreach($arguments as $arg) {
            $processed_arguments[] = $this->expr()->eq('state_id',':state_id_'.$arg);
            $this->setParameter('state_id_'.$arg,$arg,$this->getGateway()->getMetaData()->getColumn('state_id')->getType());    
        }
        
        $this->andWhere(new CompositeExpression(CompositeExpression::TYPE_OR, $processed_arguments));
        
        return $this;
    }
    
    /**
      *  Filter tranistions to occured after x
      *
      *  @access public
      *  @return TransitionQuery
      *  @param DateTime $time 
      */
    public function filterOccuredAfter(DateTime $time)
    {
        $this->andWhere($this->expr()->gte('dte_occured',':dte_occured_after'))->setParameter('dte_occured_after',$time,$this->getGateway()->getMetaData()->getColumn('dte_occured')->getType());
        
        return $this;
    }
    
    /**
      *  Filter tranistions to occured before x
      *
      *  @access public
      *  @return TransitionQuery
      *  @param DateTime $time 
      */
    public function filterOccuredBefore(DateTime $time)
    {
        $this->andWhere($this->expr()->lte('dte_occured',':dte_occured_before'))->setParameter('dte_occured_before',$time,$this->getGateway()->getMetaData()->getColumn('dte_occured')->getType());
        
        return $this;
    }
    
    /**
      *  Sort the result by the occured date
      *
      *  @access public
      *  @param string direction ASC|DESC
      *  @return TransitionQuery
      */
    public function orderByOccured($dir = 'ASC')
    {
        $this->orderBy('dte_occured',$dir);
        
        return $this;
    }
    
    /**
      *  Sort the result by the assigned State
      *
      *  @access public
      *  @param string direction ASC|DESC
      *  @return TransitionQuery
      */
    public function orderByState($dir = 'ASC')
    {
        $this->orderBy('state_id',$dir);
        
        return $this;
    }
    
    /**
      *  Group the query by the job state
      *
      *  @access public
      *  @return TransitionQuery
      */
    public function groupByState()
    {
        $this->groupBy('state_id');
        
        return $this;
    }
    
    
    /**
      *  Force Only transitions created by workers
      *
      *  @access public
      *  @return TransitionQuery
      */
    public function filterOnlyWorkers()
    {
        $this->andWhere($this->expr()->isNotNull('worker_id'));
        
        return $this;
    }
    
    /**
      *  Force only transitions created by jobs
      *
      *  @access public
      *  @return TransitionQuery
      */
    public function filterOnlyJobs()
    {
        $this->andWhere($this->expr()->isNotNull('job_id'));
        
        return $this;
    }
    
    
    
    //------------------------------------------------------------------
    # Agg functions
    
    /**
      *  Will added a calculated column that counts number of jobs in the
      *  result set, recommend use a groupBy to compress resultset
      */
    public function calculateStateCount()
    {
        $platform = $this->getConnection()->getDatabasePlatform()->getCountExpression('state_id');
        $this->addSelect($platform .' AS ' . 'state_count');
        
        return $this;
    } 
        
}
/* End of File */