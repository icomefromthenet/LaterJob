<?php
namespace Faker\Extension\Faker\Type;

use Faker\Components\Faker\Exception as FakerException;
use Faker\Components\Engine\Common\Type\Type;
use Faker\Components\Faker\Utilities;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use LaterJob\UUID;

class NullType extends Type
{

    // -------------------------------------------------------------------------

    /**
    * Generate a value
    *
    * @return string
    */
    public function generate($rows,&$values = array(),$last = array())
    {
        return null;
    }
    
   
    // -------------------------------------------------------------------------
    
    /**
    * Generates the configuration tree builder.
    *
    * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
    */
     public function getConfigTreeExtension(NodeDefinition $rootNode)
    {
        return $rootNode;
            
    }
    
    
    // -------------------------------------------------------------------------
}