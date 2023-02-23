<?php

namespace romanzipp\EnvDiff\Services;

use Dotenv\Dotenv;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;
use Wujunze\Colors;
use romanzipp\EnvDiff\Services\View\ConsoleTable;

class DiffService
{
    /**
     * File & env variables.
     *
     * @var array<string, array<string, mixed>>
     */
    private $data;

    /**
     * ConsoleTable.
     *
     * @var ConsoleTable|null
     */
    private $consoleTable;

    /**
     * Package configuration.
     *
     * @var array<string, mixed>
     */
    public $config;

    public function __construct()
    {
        $this->config = config('env-diff');

        $this->consoleTable = new ConsoleTable();
    }

    /**
     * Add a new .env file and store the loaded data.
     *
     * @param string|string[] $file File name/s
     */
    public function add($file): void
    {
        $files = is_array($file) ? $file : [$file];

        foreach ($files as $i =>$envFile) {
            #$exists = $this->markFileStatus($envFile);

            #if ($exists) {
                $this->setData(
                    $envFile,
                        Dotenv::createMutable($this->getPath(), $envFile)->load()
                    );
            /*}else {
                $this->hasMissingFiles = true;
                $this->setData(
                    $envFile,
                    [],
                );
            }*/
        }
    }

    /**
     * Get the base path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->config['path'] ?? base_path();
    }

    /**
     * Manually set variable data corresponding to file name.
     *
     * @param string $file
     * @param array<string, mixed> $data
     */
    public function setData(string $file, array $data): void
    {
        $this->data[$file] = $data;
    }

    /**
     * Get data.
     *
     * @param string|null $file
     *
     * @return array<string, mixed>
     */
    public function getData(string $file = null): array
    {
        if (null === $file) {
            return $this->data;
        }

        return $this->data[$file] ?? [];
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Create a diff of all registered env variables.
     *
     * @return array<string, array<string, bool>>
     */
    public function diff(): array
    {
        $variables = [];

        foreach ($this->data as $file => $vars) {
            foreach ($vars as $key => $value) {
                if (in_array($key, $variables, false)) {
                    continue;
                }

                $variables[] = $key;
            }
        }

        $hideExisting = $this->config['hide_existing'] ?? true;

        $diff = [];

        foreach ($variables as $variable) {
            $containing = [];

            foreach ($this->data as $file => $vars) {
                $containing[$file] = array_key_exists($variable, $vars);
            }

            if ($hideExisting) {
                $unique = array_unique(array_values($containing));

                if (1 === count($unique) && true === $unique[0]) {
                    continue;
                }
            }

            $diff[$variable] = $containing;
        }

        return $diff;
    }

    public function buildTableHeader()
    {
        $files = array_keys($this->data);

        $headers = ['Variable'];

        foreach ($files as $file) {
            $headers[] = $file;
        }

        return $headers;
    }

    public function buildTableContent()
    {
        $files = array_keys($this->data);

        $showValues = $this->config['show_values'] ?? false;

        foreach ($this->diff() as $variable => $containing) {
            $row = [$variable];

            foreach ($files as $file) {
                $value = null;

                if ( ! $showValues) {
                    $value = $this->consoleTable->valueNotFound();

                    if (true === $containing[$file]) {
                        $value = $this->consoleTable->valueOkay();
                    }
                } else {
                    $value = $this->consoleTable->getColoredString('MISSING', 'red');

                    $existing = $this->getData($file)[$variable] ?? null;

                    if (null !== $existing) {
                        $value = $existing;
                    }
                }

                $row[] = $value;
            }

            $this->consoleTable->addRow($row);
        }
    }

    public function displayTable()
    {
        $header = $this->buildTableHeader();
        $this->consoleTable->addHeader($header);
        $this->consoleTable->setUseColors($this->config['use_colors']);
        $this->buildTableContent();
        $this->consoleTable->displayTable();
    }
}
