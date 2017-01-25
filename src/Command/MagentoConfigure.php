<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoConfigure extends Command implements CommandInterface
{
    use DockerAware;

    public function configure()
    {
        $description  = "Configures Magento ready for Docker use\n\n";
        $description .= "   - Redis configuration for sessions and cache to the magento env.php file\n";
        $description .= '   - Mailcatcher configuration ready for development';

        $this
            ->setName('magento-configure')
            ->setAliases(['mc'])
            ->setDescription($description)
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'Ommits development configurations');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $phpContainer  = $this->phpContainerName();
        $mailContainer = $this->getContainerName('mail');

        system("docker exec $phpContainer magento-configure");

        $pullCommand   = $this->getApplication()->find('pull');
        $pullArguments = new ArrayInput(['files' => ['app/etc/env.php']]);

        $pullCommand->run($pullArguments, $output);

        if ($input->hasOption('prod')) {
            return;
        }

        $sql =  "DELETE FROM core_config_data WHERE path LIKE 'system/smtp/%'; ";
        $sql .= "INSERT INTO core_config_data (scope, scope_id, path, value) ";
        $sql .= "VALUES ";
        $sql .= "('default', 0, 'system/smtp/host', '$mailContainer'), ";
        $sql .= "('default', 0, 'system/smtp/port', '1025');";

        $sqlCommand   = $this->getApplication()->find('sql');
        $sqlArguments = new ArrayInput(['sql' => $sql]);

        $sqlCommand->run($sqlArguments, $output);

        $output->writeln('Configuration complete!');
    }
}
