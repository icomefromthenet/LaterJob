<?php
namespace LaterJob\Model\Monitor;

use DBALGateway\Table\AbstractTable;

/**
  *  Table Gateway for Stats
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class StatsGateway extends AbstractTable
{
    
    /**
      *  Create a new instance of the querybuilder
      *
      *  @access public
      *  @return LaterJob\Model\Storage\StorageQuery
      */
    public function newQueryBuilder()
    {
        return new StatsQuery($this->getAdapater(),$this);
    }
        
}
/* End of File */