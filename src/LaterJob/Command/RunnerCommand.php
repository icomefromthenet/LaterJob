<?php
namespace LaterJob\Command;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Helper\DialogHelper;

use LaterJob\Exception as LaterJobException,
    LaterJob\Log\ConsoleSubscriber;

use DateTime;

/**
  *  Example of a console command that run a Worker.
  *
  *  This class is used duing development to run simulations.
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class RunnerCommand extends Command
{
    
    public function floatRand($min, $max, $round=0)
    {
	if ($min > $max) {
            $min = $max;
            $max = $min;
        } else {
            $min = $min;
            $max = $max;
        }
	
        $randomfloat = $min + mt_rand() / mt_getrandmax() * ($max - $min);
	
        if($round > 0) {
		$randomfloat = round($randomfloat,$round);
        }
 
	return $randomfloat;
    }
    
    /**
    *  Example Runner for a job
    *
    * @param InputInterface $input An InputInterface instance
    * @param OutputInterface $output An OutputInterface instance
    * @return null|integer null or 0 if everything went fine, or an error code
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $this->getHelper('queue')->getQueue();
        $queue->getDispatcher()->addSubscriber(new ConsoleSubscriber($output));
        $worker = $queue->worker();
        
        
        try {
            # start the worker
            $worker->start(new DateTime());
            
            $allocator = $worker->receive(new DateTime());
            $handle = $worker->getId();
            
	    foreach($allocator as $job) {
            
                try {
                    
                    $job->start($handle,new DateTime());
                    
                    # simulate time taken by a single job to process
                    $sleep = (integer) mt_rand(0,15);    
                    sleep($sleep);        
                
                    $value = $this->floatRand(0,1);
                
                    if($value <= 0.08) {
                        $job->getStorage()->setRetryLeft(0);
                        throw new LaterJobException('failure has occured');
                    }
                
                    if($value <= 0.08) {
                        throw new LaterJobException('error has occured');
                    }
                    
                    $job->finish($handle,new DateTime());
                }
                catch(LaterJobException $e) {
                    
                    if($job->getRetryCount() > 0) {
                        $job->error($handle,new DateTime(),$e->getMessage());    
                    }
                    else {
                        $job->fail($handle,new DateTime(),$e->getMessage());    
                    }
                }
            
            }
            
	    # finish the worker
            $worker->finish(new DateTime());
            
        } catch(LaterJobException $e) {
            $worker->error($handle,new DateTime(),$e->getMessage());
            throw $e;            
        }
        
	return 0;
    }
        
}
/* End of File */