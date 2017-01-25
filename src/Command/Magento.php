<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Magento extends Command implements CommandInterface
{
    use DockerAware;

    protected function configure()
    {
        $this
            ->setName('magento')
            ->setAliases(['mage', 'm'])
            ->setDescription('Works as a proxy to the Magento bin inside the container')
            ->addArgument('cmd', InputArgument::REQUIRED, 'Magento command you want to run')
            ->ignoreValidationErrors();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $slicePoint = 1 + (int) array_search($this->getName(), $_SERVER['argv'], true);
        $args       = array_slice($_SERVER['argv'], $slicePoint);

        if (0 === count($args)) {
            throw new \RuntimeException('No magento command defined!');
        }

        $args = implode(' ', $args);

        system("docker exec -u www-data $container bin/magento $args");
    }
}
