<?php
namespace LaterJob\Model\Queue;

use DBALGateway\Table\AbstractTable;

/**
  *  Table Gateway for Stored Jobs 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class StorageGateway extends AbstractTable
{
    
    /**
      *  Create a new instance of the querybuilder
      *
      *  @access public
      *  @return LaterJob\Model\Storage\StorageQuery
      */
    public function newQueryBuilder()
    {
        return new StorageQuery($this->getAdapater(),$this);
    }
        
}
/* End of File */