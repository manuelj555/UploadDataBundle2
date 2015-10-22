<?php
/**
 * 30/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Command;

use Manuel\Bundle\UploadDataBundle\ConfigProvider;
use Manuel\Bundle\UploadDataBundle\Entity\UploadRepository;
use Psr\Log\LoggerInterface;
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
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ProcessActionCommand constructor.
     * @param ConfigProvider $configProvider
     * @param UploadRepository $uploadRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigProvider $configProvider,
        UploadRepository $uploadRepository,
        LoggerInterface $logger = null
    ) {
        $this->configProvider = $configProvider;
        $this->uploadRepository = $uploadRepository;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('uploaddata:process');
        $this->setDescription('Ejecuta el proceso de validación o transferencia de un archivo previamente cargado y leido');

        $this->addArgument('action', InputArgument::REQUIRED, 'Acción a realizar (validate, transfer)');
        $this->addArgument('id', InputArgument::REQUIRED, 'Id del Upload en la BD');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $actionName = strtolower($input->getArgument('action'));
        $id = $input->getArgument('id');

        $validActions = array('validate', 'transfer');

        if (!in_array($actionName, $validActions)) {
            $output->writeln(sprintf("<error>No existe la acción \"%s\", intente con: [%s]</error>", $id,
                join(',', $validActions)));
            return -1;
        }

        if (!$upload = $this->uploadRepository->find($id)) {
            $output->writeln(sprintf("<error>No existe el Item con id \"%s\"</error>", $id));
            return -1;
        }

        $config = $this->configProvider->get($upload->getType());

        try {
            $result = false;

            switch ($actionName) {
//                case 'read':
//                    $result = $config->processRead($upload, array());
//                    break;
                case 'validate':
                    $result = $config->processValidation($upload);
                    break;
                case 'transfer':
                    $result = $config->processTransfer($upload);
                    break;
            }

            if (false !== $result) {
                $output->writeln(sprintf("<success>Archivo procesado exitosamente</success>", $id));
            } else {
                $output->writeln(sprintf("<error>No se pudo procesar el archivo</error>", $id));
                return -1;
            }
        } catch (\Exception $e) {

            if (null !== $this->logger) {
                $this->logger->critical('No se pudo procesar la lectura del excel', array(
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ));
            }

            $output->writeln(sprintf("<error>Error al procesar el archivo</error>", $id));
            return -1;
        }
    }
}