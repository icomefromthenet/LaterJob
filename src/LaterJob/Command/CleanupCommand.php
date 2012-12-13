<?php
namespace LaterJob\Command;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Helper\DialogHelper;

use LaterJob\Exception as LaterJobException;
use DateTime;

/**
  *  Clean's up for simulation will truncate the main data tables
  *
  *  @author Lewis Dyer <getintouch@icomefromthenet.com>
  *  @since 0.0.1
  */
class CleanupCommand extends Command
{
    
    /**
    * Truncate the Queue and Transition and Monitor Tables
    *
    * @param InputInterface $input An InputInterface instance
    * @param OutputInterface $output An OutputInterface instance
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = new DialogHelper();
        $answer = $dialog->askConfirmation($output,'<info>Run Truncate</info> will <info>erase</info> stored jobs and transitions? [y|n] :',false);

        if($answer) {
            $queue = $this->getHelper('queue')->getQueue();
        
            $db_config = $queue['config.database'];
            $doctrine  = $queue['doctrine'];
        
            $output->writeln('Starting Truncate Database Tables');    
        
            $doctrine->exec('TRUNCATE ' .$doctrine->getDatabase() . '.' . $db_config->getTransitionTableName());
            $doctrine->exec('TRUNCATE ' .$doctrine->getDatabase() . '.' . $db_config->getQueueTableName());
            $doctrine->exec('TRUNCATE ' .$doctrine->getDatabase() . '.' . $db_config->getMonitorTableName());
            
            $output->writeln('Finished Truncate Database Tables');
        }
        
    }
        
}
/* End of File */