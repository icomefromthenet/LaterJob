<?php
namespace LaterJobApi\Controllers;

use Silex\Application,
    Silex\ControllerProviderInterface;
use Symfony\Component\Validator\ConstraintViolationList,
    Symfony\Component\HttpFoundation\JsonResponse;
use LaterJob\Exception as LaterJobException;


class BaseProvider implements ControllerProviderInterface
{
    
    protected $index;
    
    /**
      *  @var Silex\Application 
      */
    protected $app;
    
    /**
      *  Class Constructor
      *
      *  @access public
      *  @param string $queue_index
      */
    public function __construct($queue_index)
    {
        $this->index = $queue_index;
    }
    
    
    //------------------------------------------------------------------
    # ControllerProviderInterface
    
    public function connect(Application $app)
    {
       # bind app to his controller
        $this->app = $app;
        
        # bind errro handler
        $app->error(array($this,'handleError'));
    }
    
    
    /**
      *  Will Serialize the Error Messages From validator into a string
      *
      *  @access public
      *  @return string the error messages
      *  @param ConstraintViolationList $errors
      */
    public function serializeValidationErrors(ConstraintViolationList $errors)
    {
        $myError = array();
        if (count($errors) > 0) {
                
            foreach ($errors as $error) {
                    $myError[] = $error->getPropertyPath().' '.$error->getMessage();
            } 
        }
            
        return implode($myError,PHP_EOL);    
    }
    
    
    /**
     * Convert some data into a JSON response with specific attributes.
     *
     * @param mixed   $data    The response data
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     *
     * @see JsonResponse
     */
    public function response($data = array(), $status = 200, $headers = array())
    {
        
        if(key_exists('result',$data) === false) {
            throw new LaterJobException('Response data must have a result attribute set');
        }
        
        if(key_exists('msg',$data) === false) {
            throw new LaterJobException('Response data must have a message attribute set');
        }
        
        return new JsonResponse($data, $status, $headers);
    }
    
    
     /**
    * Error handler for exceptions if app default not been sent.
    * This handler will not be called if a app handler returns response.
    *
    * @access public
    * @return JsonResponse
    */
    public function handleError(\Exception $e, $code)
    {
        switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        case 400:
            $message = $e->getMessage();
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
        }

        # record error to app log.
        $this->app['monolog']->notice($e->getMessage());
        
        return $this->response(array('msg'=> $message,'result' => array()),$code);
    }
    
    
     /**
    * Fetch the dependency container
    *
    * @access public
    * @return Silex\Application
    */
    public function getContainer()
    {
        return $this->app;
    }
    
    
    /**
    * Fetch the symfony2 validator
    *
    * @access public
    * @return Symfony\Component\Validator\Validator
    */
    public function getValidator()
    {
        return $this->app['validator'];
    }
    
    /**
     * Fetch the queue
     * 
     *  @return LaterJob\Queue
     *  @access public
     */
    public function getQueue()
    {
       return $this->app[$this->index]; 
    }
    
    /**
      * Return the activity formatter
      *
      *  @return LaterJobApi\Formatter\ActivityFormatter
      */
    public function getActivityFormatter()
    {
         return $this->app['laterjob.api.formatters.activity']; 
    }
    
    /**
      * Return the job formatter
      *
      *  @return LaterJobApi\Formatter\JobFormatter
      */
    public function getJobFormatter()
    {
         return $this->app['laterjob.api.formatters.job']; 
    }
    
   /**
      * Return the monitor formatter
      *
      *  @return LaterJobApi\Formatter\MonitorFormatter
      */  
    public function getMonitorFormatter()
    {
        return $this->app['laterjob.api.formatters.monitor']; 
    }
    
}
/* End of File */