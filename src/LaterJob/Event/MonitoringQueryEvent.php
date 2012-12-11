<?php
namespace LaterJob\Event;

use LaterJob\Model\Monitor\Stats;
use Symfony\Component\EventDispatcher\Event;
use Traversable;
use DateTime;

/**
  *  Event class passed duing events that occur in MonitoringEventMap 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class MonitoringQueryEvent extends Event
{
   /**
     *  @var Traversable the results
     */
   protected $results;
   
   /**
     *  @var DateTime the start period 
     */
   protected $start;
   
   /**
     *  @var DateTime the end period 
     */
   protected $end;
   
   /**
     *  @var integer the query max set size 
     */
   protected $limit;
   
   /**
     *  @var integer the query offset 
     */
   protected $offset;
      
   /**
     *  @var string the order to pick ASC | DESC 
     */
   protected $order;
   
   /**
     *  @var boolean include calculating rows or only fully calculated 
     */
   protected $calculating;
   
   /**
     *  Class constructor
     *
     *  @access public
     *  
     */
   public function __construct($offset = null, $limit = null, $order = 'ASC', DateTime $start = null, DateTime $end = null, $calculating = false)
   {
      $this->start  = $start;
      $this->end    = $end;
      $this->limit  = $limit;
      $this->offset = $offset;
      $this->order  = $order;
      $this->calculating = $calculating;
   }
   
   
   /**
     *  Fetch the start time of the query
     *
     *  @access public
     *  @return DateTime
     */
   public function getStart()
   {
     return $this->start;
   }
   
   /**
     *  Return the end time of the query
     *
     *  @access public
     *  @return DateTime
     */
   public function getEnd()
   {
     return $this->end;
       
   }
   
   /**
     *  Return the query limit
     *
     *  @access public
     *  @return integer the limit
     */
   public function getLimit()
   {
     return $this->limit;
   }
   
   /**
     *  Return the query offset
     *
     *  @access public
     *  @return integer the limit
     */
   public function getOffset()
   {
     return $this->offset;
   }
  
   /**
     *  Return the query order
     *
     *  @access public
     *  @return string 'ASC | DESC'
     */
   public function getOrder()
   {
      return $this->order;
   }
   
   /**
     *  Return if to include rows being calculated
     *
     *  @access public
     *  @return boolean
     */
   public function getIncludeCalculating()
   {
      return $this->calculating;
   }
   
   /**
     *  Return the results of monitoring operation
     *
     *  @access public
     *  @return Traversable
     */
   public function getResult()
   {
       return $this->results;
   }
   
   /**
     *  Set the results of monitoring operation
     *
     *  @access public
     *  @param Traversable
     */
   public function setResult(Traversable $results)
   {
       $this->results = $results;
   }
    
}

/* End of File */