<?php


namespace Pfcode\I18nComparatorBundle\Command;


use DirectoryIterator;
use ErrorException;
use Pfcode\I18nComparatorBundle\Comparator\ComparatorFactory;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FindConflictsCommand extends Command
{
    protected static $defaultName = 'i18n-comparator:find-conflicts';

    /**
     * @var ComparatorFactory
     */
    private $comparatorFactory;

    /**
     * FindConflictsCommand constructor.
     * @param ComparatorFactory $comparatorFactory
     */
    public function __construct(ComparatorFactory $comparatorFactory)
    {
        parent::__construct();
        $this->comparatorFactory = $comparatorFactory;
    }

    protected function configure(): void
    {
        $this->addOption('translations-dir', null, InputOption::VALUE_REQUIRED,
            'Directory where translation files are stored.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ErrorException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dir = $input->getOption('translations-dir');
        if (!is_dir($dir) || !is_readable($dir)) {
            $output->writeln("Path '$dir' is not a valid directory.");
            return 2;
        }

        $comparator = $this->comparatorFactory->newComparator();

        /** @var SplFileInfo $fileinfo */
        foreach (new DirectoryIterator($dir) as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }

            $parts = explode('.', $fileinfo->getBasename());
            if (count($parts) !== 3) {
                continue;
            }

            $comparator->addTranslationFile($fileinfo->getRealPath());
        }

        /** @var Table[]|array $tables */
        $tables = [];
        foreach ($comparator->getKnownLanguageDomainPairs() as [$domain, $lang]) {
            $conflicts = $comparator->getConflicts($domain, $lang);
            if (empty($conflicts)) {
                $output->writeln("$domain.$lang: No conflicts found.");
                continue;
            }

            $headers = null;
            $rows = [];
            foreach ($conflicts as $key => $conflict) {
                if ($headers === null) {
                    $headers = ['Key'];
                    foreach ($conflict as $filepath => $translation) {
                        $headers[] = basename($filepath);
                    }
                }

                $row = [];
                $row[] = $key;
                foreach ($conflict as $translation) {
                    $row[] = $translation;
                }
                $rows[] = $row;
            }

            $table = new Table($output);
            $table->setHeaderTitle("$domain.$lang");
            $table->setHeaders($headers);
            for ($i = 1, $iMax = count($headers); $i < $iMax; $i++) {
                $table->setColumnMaxWidth($i, 80);
            }
            $table->setRows($rows);
            $tables[] = $table;
        }

        if (empty($tables)) {
            $output->writeln('No conflicts found.');
            return 0;
        }

        foreach ($tables as $table) {
            $table->render();
        }

        return 0;
    }
}