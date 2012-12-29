<?php
namespace LaterJobApi\Controllers;

use DateTime;
use Silex\Application,
    Silex\ControllerProviderInterface;
use LaterJob\Exception as LaterJobException,
    LaterJob\Model\Activity\Transition;
use Symfony\Component\Validator\Constraints as Assert,
    Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Collection;
    
class ActivityProvider extends BaseProvider implements ControllerProviderInterface
{
    
    const QUERY_LIMIT = 500;
    
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('activities', array($this,'getActivities'));
        $controllers->delete('activities', array($this,'deleteActivities'));

        return $controllers;
    }
    
    /**
      *  Fetch Activitiy history 
      *
      *  @access public
      *  @param Silex\Application $app
      */
    public function getActivities(Application $app, Request $request)
    {
        $format = $app['laterjob.api.formatters.activity'];
        $data = array(
            'result' => array(),
            'msg' => null
        );  

        $code = 200;
        
        try {
            
            $validator = $app['validator'];
        
            # gater query params
            
            $offset = $request->get('offset',0);
            $limit  = $request->get('limit',100);
            $order  = $request->get('order','asc');
            $before = $request->get('before');
            $after  = $request->get('after');
        
            # filter query params and assign default values
            $constraint = new Assert\Collection(array(
                                'offset' => new Assert\Range(array('min' =>0 ,'max' => PHP_INT_MAX)),
                                'limit'  => new Assert\Range(array('min' =>1 ,'max' =>self::QUERY_LIMIT)),
                                'order'  => new Assert\Choice(array( 'choices' => array('desc','asc') )),
                                'before' => new Assert\DateTime(),
                                'after'  => new Assert\DateTime()
                            ));
            
            
            $errors = $app['validator']->validateValue(array(
                                                 'offset' => $offset,
                                                 'limit'  => $limit,
                                                 'order'  => $order,
                                                 'before' => $before,
                                                 'after'  => $after
                                                  ), $constraint);
            
            if (count($errors) > 0) {
                throw new LaterJobException($this->serializeValidationErrors($errors));
            }
            
            if($before !== null) {
                $before = new DateTime($before);    
            }
            
            if($after !== null) {
                $after = new DateTime($after);
            }
            
            # run against api
            $result = $app[$this->index]->activity()->query((int)$offset,(int)$limit,$order,$before,$after);
            $data['msg']    = true;
            
            # convert using formatter if known type.
            if($result instanceof Collection) {
                $data['result'] = $format->toArrayCollection($result);
            } elseif($result instanceof Transition) {
                $data['result'] = array($format->toArray($result));
            } elseif($result === null || $result === false) {
                $data['result'] = array();
            } else {
                throw new LaterJobException('return data is in an unknown type : ' . gettype($result));
            }
            
            
        } catch(LaterJobException $e) {
            $data['msg'] = $e->getMessage();
            $code = 500;
            $app['monolog']->notice($e->getMessage());
        } catch (\Exception $e) {
            $data['msg'] = $e->getMessage();
            $code = 500;
            $app['monolog']->notice($e->getMessage());
        }
        
        return $this->response($data, $code);
    }
    
    /**
      *  Remove Activitiy history ie a purge
      *
      *  @access public
      *  @param Silex\Application $app
      */
    public function deleteActivities(Application $app, Request $request)
    {
         $data = array(
            'result' => array(),
            'msg' => null
        );  

        $code = 200;
        
        try {
            
            $validator  = $app['validator'];
            $before     = $request->get('before');
            $constraint = new Assert\Collection(array('before' => new Assert\DateTime(),));
            
            $errors     = $app['validator']->validateValue(array('before' =>$before), $constraint);
            
            if (count($errors) > 0) {
                throw new LaterJobException($this->serializeValidationErrors($errors));
            }
            
            # run against api
            $data['result'] = $app[$this->index]->activity()->purge(new DateTime($before));
            $data['msg']    = true;
            
        } catch(LaterJobException $e) {
            $data['msg'] = $e->getMessage();
            $code = 500;
            $app['monolog']->notice($e->getMessage());
        } catch (\Exception $e) {
            $data['msg'] = $e->getMessage();
            $code = 500;
            $app['monolog']->notice($e->getMessage());
        }
        
        return $this->response($data, $code);
        
    }
    
}
/* End of File */