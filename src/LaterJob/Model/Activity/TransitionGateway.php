<?php
namespace LaterJob\Model\Activity;

use DBALGateway\Table\AbstractTable;

/**
  *  Table Gateway for transitions 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class TransitionGateway extends AbstractTable
{
    
    
    /**
      *  Create a new instance of the querybuilder
      *
      *  @access public
      *  @return LaterJob\Model\Activity\TransitionQuery
      */
    public function newQueryBuilder()
    {
        return new TransitionQuery($this->getAdapater(),$this);
    }
        
}
/* End of File */