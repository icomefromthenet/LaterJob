<?php
namespace Faker\Extension\Faker\Type;

use Faker\Components\Faker\Exception as FakerException,
    Faker\Components\Faker\Type\Type,
    Faker\Components\Faker\Utilities,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition,
    Symfony\Component\Config\Definition\Builder\TreeBuilder;
use LaterJob\UUID;
use LaterJob\Util\MersenneRandom;

class Job extends Type
{

    static protected $uuid;

    // -------------------------------------------------------------------------

    /**
    * Generate a value
    *
    * @return string
    */
    public function generate($rows,$values = array())
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
    public function getConfigExtension(ArrayNodeDefinition $rootNode)
    {
        return $rootNode;
            
    }
    
    
    // -------------------------------------------------------------------------
}