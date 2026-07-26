<?php
namespace Apie\Common\Command;

use Apie\Common\Translator\TranslationCollector;
use Apie\Core\Other\FileWriterInterface;
use Composer\Console\Input\InputArgument;
use Composer\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ApieMakeTranslationFileCommand extends Command
{
    public function __construct(
        protected readonly TranslationCollector $collector,
        protected readonly FileWriterInterface $fileWriter,
    ) {
        parent::__construct('apie:make-translation-file');
    }
    protected function configure(): void
    {
        $this->setDescription('This command will create a translation file for Apie.');
        $this->addArgument('filename', InputArgument::OPTIONAL, 'filename to write', 'php://stdout');
        $this->addOption('specifity-filter', 's', InputOption::VALUE_OPTIONAL, 'Only create text with a certain specifity to reduce number of texts', 99);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('filename');
        $method = 'renderPhpFile';
        if ($filename === 'php://stdout') {
            $output = new NullOutput();
        } else {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if ($ext) {
                 $method = match($ext) {
                    'php' => 'renderPhpFile',
                    'json' => 'renderJsonFile',
                    'yml' => 'renderYamlFile',
                    'yaml' => 'renderYamlFile',
                    default => throw new \LogicException("Unknown extension " . $ext),
                };
            } else {
                $filename .= '.php';
            }
        }
        $output->writeln('Collecting all translations...');
        $translationList = $this->collector->createList();
        $output->writeln('Done');
        $output->writeln('Creating file....');
        $this->$method($filename, $translationList->toNestedArray());
        $output->writeln('Created ' . $filename . ' successfully');
        return Command::SUCCESS;
    }

    /**
     * @param array<array-key, mixed> $input
     */
    private function renderPhpFile(string $filename, array $input): void
    {
        $this->fileWriter->writeFile(
            $filename,
            "<?php" . PHP_EOL . "return " . var_export($input, true) . ';'
        );
    }

    /**
     * @param array<array-key, mixed> $input
     */
    private function renderJsonFile(string $filename, array $input): void
    {
        $this->fileWriter->writeFile(
            $filename,
            json_encode($input, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT)
        );
    }

    /**
     * @param array<array-key, mixed> $input
     */
    private function renderYamlFile(string $filename, array $input): void
    {
        $this->fileWriter->writeFile(
            $filename,
            Yaml::dump($input, inline: 9)
        );
    }
}
