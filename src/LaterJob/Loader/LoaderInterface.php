<?php
namespace LaterJob\Loader;

use Pimple\Container;

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
    *  @param Pimple\Container $queue
    */
    public function boot(Container $queue);
    
}

/* End of File */