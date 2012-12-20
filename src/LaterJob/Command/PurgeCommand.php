<?php
namespace LaterJob\Command;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Helper\DialogHelper,
    Symfony\Component\Console\Input\InputArgument;

use LaterJob\Exception as LaterJobException,
    LaterJob\Log\ConsoleSubscriber; 

use DateTime;

/**
  *  Purge Actvity before the given date.
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class PurgeCommand extends Command
{
    
    /**
    * Truncate the Queue and Transition table
    *
    * @param InputInterface $input An InputInterface instance
    * @param OutputInterface $output An OutputInterface instance
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date  = new DateTime($input->getArgument('date'));
        
        $output->writeln('app:purge is <info>starting</info>');
        
        $this->getHelper('queue')->getQueue()->getDispatcher()->addSubscriber(new ConsoleSubscriber($output));
        
        
        $output->writeln('app:purge running for period <info>'.$date->format('Y-m-d H:i:s') .'</info>');
           
        $this->getHelper('queue')->getQueue()->activity()->purge($date);      
        
        $output->writeln('app:purge finished running purge');
        
        return true;
    }
    
    protected function configure()
    {
        $this->setDescription('Will run the Purge Sequence');
        $this->setHelp(<<<EOF
Will run the purge for the date

Examples:

>> app:purge '15th january 2011'
>> app:purge 'today -1 month'


First Argument is date to purge before, should use php strtotime syntax
make sure to quote or date will not parse correctly.

EOF
);
        $this->addArgument('date',InputArgument::REQUIRED, 'A strtotime date string to purge </info>before<info>');
        parent::configure();
    }
        
}
/* End of File */