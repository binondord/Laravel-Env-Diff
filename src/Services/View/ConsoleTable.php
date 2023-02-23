<?php

namespace romanzipp\EnvDiff\Services\View;

use Dotenv\Dotenv;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;
use Wujunze\Colors;
use romanzipp\EnvDiff\Services\DiffService;

class ConsoleTable
{
    /**
     * Console table.
     *
     * @var Table|null
     */
    private $table;

    /**
     * @var \Symfony\Component\Console\Output\BufferedOutput
     */
    private $output;

    /**
     * File & env variables.
     *
     * @var array<string, array<string, mixed>>
     */
    private $data;

    /**
     * Package configuration.
     *
     * @var array<string, mixed>
     */
    public $config;

    public bool $useColors = false;

    public function __construct()
    {
        $this->table = new Table(
            $this->output = new BufferedOutput()
        );
    }

    public function setUseColors($useColors)
    {
        $this->useColors = $useColors;
    }

    public function addRow($row)
    {
        $this->table->addRow($row);
    }

    public function addHeader(array $headers): void
    {
        $this->table->setHeaders($headers);
    }

    /**
     * Build & display table.
     *
     * @return void
     */
    public function displayTable(): void
    {
        $this->table->render();

        echo $this->output->fetch();
    }

    /**
     * Color a string for shell output if enabled via config.
     *
     * @param string $string
     * @param string $color
     *
     * @return string
     */
    public function getColoredString(string $string, string $color): string
    {
        if ( ! $this->useColors) {
            return $string;
        }

        return (new Colors())->getColoredString($string, $color);
    }

    /**
     * Get console table string value.
     *
     * @return string
     */
    public function valueOkay(): string
    {
        return $this->getColoredString('Y', 'green');
    }

    /**
     * Get console table string value.
     *
     * @return string
     */
    public function valueNotFound(): string
    {
        return $this->getColoredString('N', 'red');
    }
}