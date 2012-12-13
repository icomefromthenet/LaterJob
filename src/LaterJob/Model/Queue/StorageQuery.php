<?php
namespace LaterJob\Model\Queue;

use DBALGateway\Query\AbstractQuery;
use DateTime;
use LaterJob\Config\QueueConfig;

/**
  *  Query class for Stored Jobs 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class StorageQuery extends AbstractQuery
{
    
    /**
      *  Filter only incude a particular job
      *
      *  @access public
      *  @return StorageQuery
      *  @param string $id of the worker
      */
    public function filterByJob($id)
    {
        $this->where($this->expr()->eq('job_id',':job_id'))->setParameter('job_id',$id,$this->getGateway()->getMetaData()->getColumn('job_id')->getType());
        
        return $this;
    }
    
    /**
      *  Filter The results by a single state
      *
      *  @return StorageQuery
      *  @param integer the state id
      */
    public function filterByState($state_id)
    {
        $this->andWhere($this->expr()->eq('state_id',':state_id'))->setParameter('state_id',$state_id,$this->getGateway()->getMetaData()->getColumn('state_id')->getType());
        
        return $this;
    }
    
    
    /**
      *  Filter jobs that have the following state
      *
      * @return StorageQuery
      * @access public
      */
    public function filterByStateAdd()
    {
        $this->orWhere($this->expr()->eq('state_id',':state_id_added'))->setParameter('state_id_added',QueueConfig::STATE_ADD,$this->getGateway()->getMetaData()->getColumn('state_id')->getType());
        
        return $this;
    }
    
    /**
      *  Filter jobs that have the following state
      *
      * @return StorageQuery
      * @access public
      */
    public function filterByStateStart()
    {
        $this->orWhere($this->expr()->eq('state_id',':state_id_started'))->setParameter('state_id_started',QueueConfig::STATE_START,$this->getGateway()->getMetaData()->getColumn('state_id')->getType());
        
        return $this;
    }
    
    /**
      *  Filter jobs that have the following state
      *
      * @return StorageQuery
      * @acc public
      */
    public function filterByStateFinish()
    {
        $this->orWhere($this->expr()->eq('state_id',':state_id_finished'))->setParameter('state_id_finished',QueueConfig::STATE_FINISH,$this->getGateway()->getMetaData()->getColumn('state_id')->getType());
        
        return $this;
    }
    
    /**
      *  Filter jobs that have the following state
      *
      *  @return StorageQuery
      *  @access public
      */
    public function filterByStateError()
    {
        $this->orWhere($this->expr()->eq('state_id',':state_id_error'))->setParameter('state_id_error',QueueConfig::STATE_ERROR,$this->getGateway()->getMetaData()->getColumn('state_id')->getType());
        
        return $this;
    }
    
    /**
      *  Filter jobs that have the following state
      *
      *  @return StorageQuery
      *  @access public
      *  @param DateTime $now
      */
    public function filterByStateErrorPassedRetryWait(DateTime $now)
    {
        $this->orWhere($this->expr()->andX($this->expr()->eq('state_id',':state_id_error'),$this->expr()->lte('retry_last',':retry_last_ltenow')))
             ->setParameter('state_id_error',QueueConfig::STATE_ERROR,$this->getGateway()->getMetaData()->getColumn('state_id')->getType())
             ->setParameter('retry_last_ltenow',$now,$this->getGateway()->getMetaData()->getColumn('retry_last')->getType());
        
        return $this;
    }
    
    /**
      *  Filter jobs that have the following state
      *
      *  @access public
      *  @return StorageQuery
      */
    public function filterByStateFail()
    {
        $this->orWhere($this->expr()->eq('state_id',':state_id_failed'))->setParameter('state_id_failed',QueueConfig::STATE_FAIL,$this->getGateway()->getMetaData()->getColumn('state_id')->getType());
        
        return $this;
    }
    
    /**
      *  Filter the results to jobs that have the following lockout
      *
      *  @access public
      *  @return StorageQuery
      */
    public function filterByLockout($lock)
    {
        $this->andWhere($this->expr()->eq('handle',':lockout_handle'))->setParameter('lockout_handle',$lock,$this->getGateway()->getMetaData()->getColumn('handle')->getType());
        
        return $this;
    }
    
    /**
      *  Filter the results to jobs that have the following lockout
      *
      *  @access public
      *  @return StorageQuery
      */
    public function filterByEmptyLockout()
    {
        $this->andWhere($this->expr()->isNull('handle'));
        
        return $this;
    }
    
    /**
      *  Filter the results to optionally include expired lockouts
      *
      *  @access public
      *  @return StorageQuery
      *  @param DateTime $expired
      */
    public function filterByExpiredLockout(DateTime $expiry)
    {
        $this->andWhere($this->expr()->lte('lock_timeout',':lock_timeout_exp'))->setParameter('lock_timeout_exp',$expiry,$this->getGateway()->getMetaData()->getColumn('lock_timeout')->getType());
        
        return $this;
    }
    
    /**
      *  Filter by an empty or expired lockout 
      */
    public function filterByExpiredOrEmptyLockout(DateTime $expiry)
    {
       $this->andWhere($this->expr()->orX($this->expr()->isNull('handle'),$this->expr()->lte('lock_timeout',':lock_timeout_exp')))->setParameter('lock_timeout_exp',$expiry,$this->getGateway()->getMetaData()->getColumn('lock_timeout')->getType());
       return $this; 
    }
    
    /**
      *  Filter the results to jobs that have lockout timer after x
      *
      *  @access public
      *  @return StorageQuery
      */
    public function filterByLockTimmerAfter(DateTime $time)
    {
        $this->andWhere($this->expr()->gte('lock_timeout',':lock_timeout_after'))->setParameter('lock_timeout_after',$time,$this->getGateway()->getMetaData()->getColumn('lock_timeout')->getType());
        
        return $this;
        
    }
    
    /**
      *  Filter the results to jobs that have the have a lockout timer before x
      *  
      *  @access public
      *  @return StorageQuery
      */
    public function filterByLockTimmerBefore(DateTime $time)
    {
        $this->andWhere($this->expr()->lte('lock_timeout',':lock_timeout_before'))->setParameter('lock_timeout_before',$time,$this->getGateway()->getMetaData()->getColumn('lock_timeout')->getType());
        
        return $this;
        
    }
    
     /**
      *  Filter jobs added to queue after x
      *
      *  @access public
      *  @return StorageQuery
      *  @param DateTime $time 
      */
    public function filterByAddedAfter(DateTime $time)
    {
        $this->andWhere($this->expr()->gte('dte_add',':dte_occured_after'))->setParameter('dte_occured_after',$time,$this->getGateway()->getMetaData()->getColumn('dte_add')->getType());
        
        return $this;
    }
    
    /**
      *  Filter jobs added to queue before x
      *
      *  @access public
      *  @return StorageQuery
      *  @param DateTime $time 
      */
    public function filterByAddedBefore(DateTime $time)
    {
        $this->andWhere($this->expr()->lte('dte_add',':dte_occured_before'))->setParameter('dte_occured_before',$time,$this->getGateway()->getMetaData()->getColumn('dte_add')->getType());
        
        return $this;
    }
    
    //------------------------------------------------------------------
    # Order By Queries
    
    /**
      *  Order the result by the date job added to queue
      *
      *  @access public
      *  @return StorageQuery
      */
    public function orderByDateAdded($dir = 'ASC')
    {
        $this->orderBy('dte_add',$dir);
        
        return $this;
    }
    
    
    //------------------------------------------------------------------
    # GroupByQueries
    
    /**
      *  Group the results by their state value
      *
      *  @access public
      *  @return StorageQuery
      */
    public function groupByState()
    {
        $this->groupBy('state_id');
        
        return $this;
    }
    
    /**
      *  Group the results by their lockout timmer
      *
      *  @access public
      *  @return StorageQuery
      */
    public function groupByLockoutHandle()
    {
        $this->groupBy('handle');
        
        return $this;
    }
    
    
    //------------------------------------------------------------------
    # Agg functions
    
    /**
      *  Will added a calculated column that counts number of jobs in the
      *  result set, recommend use a groupBy to compress resultset
      */
    public function calculateJobCount()
    {
        $platform = $this->getConnection()->getDatabasePlatform()->getCountExpression('job_id');
        $this->addSelect($platform .' AS ' . 'job_count');
        
        return $this;
    }
    
    
    
}
/* End of File */