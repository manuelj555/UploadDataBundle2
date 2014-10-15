<?php
/**
 * 30/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Command;

use Manuelj555\Bundle\UploadDataBundle\ConfigProvider;
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ProcessActionCommand extends Command
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;
    /**
     * @var UploadRepository
     */
    protected $uploadRepository;

    public function __construct($configProvider, $uploadRepository)
    {
        $this->configProvider = $configProvider;
        $this->uploadRepository = $uploadRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Id del Upload en la BD');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processName = strtolower($input->getArgument('name'));
        $id = $input->getArgument('id');

        if (!$upload = $this->uploadRepository->find($id)) {
            $output->writeln(sprintf("<error>No existe el Item con id \"%s\"</error>", $id));
            return -1;
        }

        $config = $this->configProvider->get($upload->getType());

        switch($processName){
            case 'read':
                $config->processRead($upload, array());
            case 'validate':
                $config->processValidation($upload);
            case 'transfer':
                $config->processTransfer($upload);
        }
    }


}