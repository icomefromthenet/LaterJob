<?php
namespace LaterJob\Model\Monitor;

use DBALGateway\Query\AbstractQuery;
use DateTime;

/**
  *  Query class for Monitor results 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class StatsQuery extends AbstractQuery
{
    
    /**
      *  Filter only incude a particular monitor result
      *
      *  @access public
      *  @return StatsQuery 
      *  @param string $id of the worker
      */
    public function filterByMonitor($id)
    {
        $this->where($this->expr()->eq('monitor_id',':monitor_id'))->setParameter('monitor_id',$id,$this->getGateway()->getMetaData()->getColumn('monitor_id')->getType());
        
        return $this;
    }
    
    /**
      *  Filter the result to a particular date
      *
      *  @access public
      *  @param DateTime $date the date to find
      *  @return StatsQuery 
      */
    public function filterByCoverDate(DateTime $date)
    {
        $this->where($this->expr()->eq('monitor_dte',':monitor_dte'))->setParameter('monitor_dte',$date,$this->getGateway()->getMetaData()->getColumn('monitor_dte')->getType());
        
        return $this;
    }
  
  
    /**
      *  Filter the results that occure before date x
      *
      *  @access public
      *  @param DateTime $before the date to find
      *  @return StatsQuery 
      */
    public function andWhereCoverDateBefore(DateTime $before)
    {
        $this->andWhere($this->expr()->lte('monitor_dte',':monitor_dte_before'))->setParameter('monitor_dte_before',$before,$this->getGateway()->getMetaData()->getColumn('monitor_dte')->getType());
        
        return $this;
    }
    
    
         
    /**
      *  Filter the results that occure after date x
      *
      *  @access public
      *  @param DateTime $before the date to find
      *  @return StatsQuery 
      */
    public function andWhereCoverDateAfter(DateTime $after)
    {
        $this->andWhere($this->expr()->gte('monitor_dte',':monitor_dte_after'))->setParameter('monitor_dte_after',$after,$this->getGateway()->getMetaData()->getColumn('monitor_dte')->getType());
        
        return $this;
    }
    
    
    /**
      *  Filter the results that are complete ie not locked
      *
      *  @access public
      *  @return StatsQuery 
      */
    public function andWhereLocked()
    {
        $this->andWhere($this->expr()->eq('monitor_complete',':monitor_complete'))->setParameter('monitor_complete',true,$this->getGateway()->getMetaData()->getColumn('monitor_complete')->getType());
        
        return $this;
    }
    
    /**
      *  Filter the results that are not complete only locked
      *
      *  @access public
      *  @return StatsQuery
      */
    public function andWhereUnlocked()
    {
        $this->andWhere($this->expr()->eq('monitor_complete',':monitor_complete'))->setParameter('monitor_complete',false,$this->getGateway()->getMetaData()->getColumn('monitor_complete')->getType());
        
        return $this;
    }
    
}
/* End of File */