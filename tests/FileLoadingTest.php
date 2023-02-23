<?php

namespace romanzipp\EnvDiff\Tests;

use Dotenv\Exception\InvalidPathException;
use romanzipp\EnvDiff\Services\DiffService;
use romanzipp\EnvDiff\Tests\TestCases\TestCase;

class FileLoadingTest extends TestCase
{
    public function testMissingFile()
    {
        $this->expectException(InvalidPathException::class);

        $service = new DiffService();
        $service->add('.env.missing');
    }

    public function testWrongDataGetter()
    {
        $service = new DiffService();
        $service->add('.env');

        $this->assertEquals([], $service->getData('.env.missing'));
    }

    public function testSingleFileSingleVariable()
    {
        $service = new DiffService();
        $service->add('.env');

        $this->assertEquals([
            'ENV' => 'development',
        ], $service->getData('.env'));
    }

    public function testSingleFileMultipleVariables()
    {
        $service = new DiffService();
        $service->add('.env.second');

        $this->assertEquals([
            'FOO' => 'bar',
            'BAR' => '1',
        ], $service->getData('.env.second'));
    }
}
