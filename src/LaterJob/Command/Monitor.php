<?php
namespace LaterJob\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use LaterJob\Exception as LaterJobException;
use DateTime;

/**
  *  Runs the Monitor
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class Monitor extends Command
{
    
    /**
      *  Attempt to get and parse the optional date argument
      *  
      *  @access protected
      *  @return DateTime  | null
      */
    protected function getDateArgument(InputInterface $input)
    {
        $date = new DateTime();
        
        if($input->getArgument('date') !== null) {
            $date = new DateTime($input->getArgument('date'));
        }
        
        $date->setTime($date->format('H'),0,0);        
        
        return $date;
        
    }
    
    
    /**
    * Truncate the Queue and Transition and Monitor Tables
    *
    * @param InputInterface $input An InputInterface instance
    * @param OutputInterface $output An OutputInterface instance
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('app:monitor is <info>starting</info>');
        
        $monitor               = $this->getHelper('queue')->getQueue()->monitor();
        $last_monitor_results  = $monitor->query(0,1,'DESC',null,null);
        $date                  = $this->getDateArgument($input); // return a parsed arg or now.
        
        if(count($last_monitor_results) === 1) {

            if($last_monitor_results[0]->getMonitorDate()->getTimestamp() >= $date->getTimestamp()) {
                $output->writeln('app:monitor already run for the period <info>'.$date->format('Y-m-d H:i:s').'</info> exiting');
                return false;
            }
        }
        
        $output->writeln('app:monitor running for period <info>'.$date->format('Y-m-d H:i:s') .'</info>');
       
        # execute API Method         
        $monitor->monitor($date);
        
        return true;
    }
    
    protected function configure()
    {
        $this->setDescription('Will run the Monitor Sequence');
        $this->setHelp(<<<EOF
Will run the monitor sequence to observe a period:

Example

>> app:monitor '15th january 2011'

First Argument is optional date to monitor on, should use php strtotime syntax
make sure to quote or date will not parse correctly.

EOF
);
        $this->addArgument('date',InputArgument::OPTIONAL, 'A strtotime date string to observe');
        parent::configure();
    }
        
}
/* End of File */