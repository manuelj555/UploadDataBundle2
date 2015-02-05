<?php
/**
 * 30/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Command;

use Manuel\Bundle\UploadDataBundle\ConfigProvider;
use Manuel\Bundle\UploadDataBundle\Entity\UploadRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ProcessReadCommand extends ProcessActionCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('upload_data:process:read')
        ->addArgument('options');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');

        if (!$upload = $this->uploadRepository->find($id)) {
            $output->writeln(sprintf("<error>No existe el Item con id \"%s\"</error>", $id));

            return -1;
        }

        $config = $this->configProvider->get($upload->getType());

        $config->processRead($upload);
    }


}