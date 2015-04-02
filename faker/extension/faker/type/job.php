<?php
namespace Faker\Extension\Faker\Type;

use Faker\Components\Faker\Exception as FakerException;
use Faker\Components\Faker\Utilities;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use LaterJob\UUID;
use LaterJob\Util\MersenneRandom;
use Faker\Components\Engine\Common\Type\Type;

class Job extends Type
{

    static protected $uuid;

    // -------------------------------------------------------------------------

    /**
    * Generate a value
    *
    * @return string
    */
    public function generate($rows,&$values = array(),$last = array())
    {
        if(self::$uuid === null) {
            self::$uuid = new UUID(new MersenneRandom(1000));
        }
        
        return self::$uuid->v3(self::$uuid->v4(),uniqid());
        
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