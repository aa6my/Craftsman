<?php 

namespace Codeigniter\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use Symfony\Component\Process\Process;

use Exception;

/**
* Migration:Run
*/
class Run extends Command
{
	/**
	 * [$_valid_works description]
	 * @var array
	 */
	private $_valid_works = array(
		'current',
		'version',
		'latest'
	);
	/**
	 * [$_work description]
	 * @var [type]
	 */
	private $_work;

	/**
	 * [$_module description]
	 * @var [type]
	 */
	private $_module;

	/**
	 * [$_commands description]
	 * @var array
	 */
	private $_commands = array(
		'module' => '/usr/bin/env php index.php migration module',
		'default' => '/usr/bin/env php index.php migration'
	);

	/**
	 * [$_version description]
	 * @var integer
	 */
	private $_version = 0;

    /**
     * [configure description]
     * @return [type] [description]
     */
    protected function configure()
    {
        $this
            ->setName('migration:run')
            ->setDescription('Migration Run')
            ->addArgument(
                'work',
                InputArgument::REQUIRED,
                'Set the work name'
            )
            ->addArgument(
            	'version',
            	InputArgument::OPTIONAL,
            	'Set current version',
            	NULL
            )
            ->addOption(
                'module', 
                'm', 
                InputOption::VALUE_REQUIRED, 
                'Set the module name', 
                FALSE
            )
        ;
    }

    /**
     * [execute description]
     * @param  InputInterface  $input  [description]
     * @param  OutputInterface $output [description]
     * @return [type]                  [description]
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$this->_work = $input->getArgument('work');
    	$this->_version = $input->getArgument('version');
        $this->_module = $input->getOption('module');
		
		$output->writeln('<info>Work: '.$this->_work.'</info>');
        if (! in_array($this->_work, $this->_valid_works)) 
        {
        	$output->writeln('<error>The work: '.$this->_work.' is not valid.</error>');
        	return;
        }
        elseif ($this->_module !== FALSE && is_string($this->_module)) 
        {
        	$output->writeln('<info>Module: '.$this->_module.'</info>');
        	if ($this->_work == 'version') 
        	{
        		$this->_version = abs($this->_version);
        		$output->writeln('<info>Version: '.$this->_version.'</info>');
        		$command = $this->_commands["module"]." {$this->_module} {$this->_work} {$this->_version}";
        	} 
        	else 
        	{
        		$command = $this->_commands["module"]." {$this->_module} {$this->_work}";
        	}
        }
        else
        {
        	if ($this->_work == 'version') 
        	{
				$this->_version = abs($this->_version);
        		$output->writeln('<info>Version: '.$this->_version.'</info>');
        		$command = $this->_commands["default"]." {$this->_work} {$this->_version}";
        	} 
        	else 
        	{
        		$command = $this->_commands["default"]." {$this->_work}";
        	}
        }
		$helper = $this->getHelper('question');
		$question = new ConfirmationQuestion('Continue with this action <comment>[yes]</comment>? ', TRUE);		
		if (!$helper->ask($input, $output, $question)) {
		    return;
		}
        $process = new Process($command);
        $process->run();

		// executes after the command finishes
		if (!$process->isSuccessful()) {
		    throw new \RuntimeException($process->getErrorOutput());
		}		

		echo $process->getOutput();        
    }	
}
