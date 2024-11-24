<?php
declare(strict_types=1);

namespace MageOS\PageBuilderTemplateImportExport\Console\Command;

use Magento\Framework\Console\Cli;
use MageOS\PageBuilderTemplateImportExport\Api\TemplateManagementInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class ImportTemplate extends \Symfony\Component\Console\Command\Command
{
    const IMPORT_PATH = 'filePath';

    /**
     * @param TemplateManagementInterface $templateManagement
     */
    public function __construct(
        private readonly TemplateManagementInterface $templateManagement
    ) {
        return parent::__construct();
    }

    protected function configure()
    {
        $options = [
            new InputOption(
                self::IMPORT_PATH,
                null,
                InputOption::VALUE_REQUIRED,
                'Template import file path'
            )
        ];
        $this->setName('pagebuilder:template:import');
        $this->setDefinition($options);
        $this->setDescription('Import PageBuilder Template.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setDecorated(true);
        $templateId = $this->templateManagement->importTemplateFromArchive($input->getOption(self::IMPORT_PATH));

        if ($templateId) {
            $output->writeln("Template archive imported correctly");
            return Cli::RETURN_SUCCESS;
        }

        $output->writeln("An error occurred importing the template archive");
        return Cli::RETURN_FAILURE;
    }
}
