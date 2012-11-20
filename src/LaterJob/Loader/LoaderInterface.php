<?php
namespace LaterJob\Loader;

use Pimple;

/**
  *  Interface for loader objects 
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
interface LoaderInterface
{
    
    /**
    *  Bootstrap the Dependecies
    *
    *  @access public
    *  @param Pimple $queue
    */
    public function boot(Pimple $queue);
    
}

/* End of File */