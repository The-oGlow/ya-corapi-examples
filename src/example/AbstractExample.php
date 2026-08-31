<?php

declare(strict_types=1);

/*
 * This file is part of yacorapi-examles
 *
 * (c) 2024 Oliver Glowa, coding.glowa.com
 *
 * This source file is subject to the Apache-2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace oglow\example;

use Monolog\ConsoleLogger;
use Monolog\PlainLogger;
use oglow\tools\Yacorapi\IResponse;
use oglow\tools\Yacorapi\Store\CsvFileAdapter;
use oglow\tools\Yacorapi\Store\FileAdapter;
use ollily\Tools\String\ImplodeTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class AbstractExample
{
    use ImplodeTrait;

    /** Default output level (DEBUG) */
    public const string LEVEL_DEFAULT = LogLevel::INFO;

    protected PlainLogger $output;

    private LoggerInterface $logger;

    private string $outputFileName;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));
        $this->logger->debug("START");

        $this->outputFileName = empty($outputFileName) ? get_class($this) : $outputFileName;
        $this->output = new PlainLogger(get_class($this));

        $this->logger->debug("END");
    }

    protected function outputLine(string $line, ?int $idx = null): void
    {
        $prefix = '';
        if (isset($idx)) {
            $prefix = sprintf("%s;", $idx);
        }
        $this->output->out($prefix . $line);
    }

    /**
     * @param mixed    $anyData
     * @param null|int $idx
     */
    protected function outputData(mixed $anyData, ?int $idx = null): void
    {
        $prefix = '';
        if (isset($idx)) {
            $prefix = sprintf("%s;", $idx);
        }
        if (is_a($anyData, IResponse::class)) {
            $this->output->out($prefix . "$anyData");
        } else {
            $this->output->out($prefix . self::implode_recursive(",", $anyData, false, true));
        }
    }

    protected function outputDatas(?IResponse $response): void
    {
        $idx = 1;
        // FIXME: IResponse liefert falschen Wert.
        if (!empty($response)) {
            if ($response->getResults()->count() > 0) {
                foreach ($response->getResults() as $singleResult) {
                    if ($singleResult instanceof IResponse) {
                        $this->outputData($singleResult->getValue(IResponse::KEY_ID), $idx++);
                    } else {
                        $this->outputData([$singleResult[IResponse::KEY_ID],
                            $singleResult[IResponse::KEY_SPACE][IResponse::KEY_KEY], $singleResult[IResponse::KEY_TITLE]], $idx++);
                    }
                }
            } else {
                $this->output->out('Empty results');
            }
        } else {
            $this->output->out('Empty response');
        }
    }

    protected function prepareTargetFileName(string $suffix = ''): string
    {
        $fileName = basename($this->outputFileName);
        if (!empty($suffix)) {
            $fileName .= '-' . $suffix;
        }

        return $fileName;
    }

    /**
     * @param mixed  $anyData
     * @param string $fileExtension
     */
    protected function storeOrg(mixed $anyData, string $fileExtension = 'txt'): void
    {
        $fileAdapter = new FileAdapter($this->outputFileName, $fileExtension);
        $fileAdapter->storeData($anyData);
    }

    /**
     * @param mixed  $anyData
     * @param string $fileExtension
     */
    protected function storeMod(mixed $anyData, string $fileExtension = 'txt'): void
    {
        $fileAdapter = new FileAdapter($this->outputFileName, $fileExtension);
        $fileAdapter->storeData($anyData);
    }

    /**
     * @param mixed  $anyData
     * @param string $fileExtension
     */
    protected function storeAsDump(mixed $anyData, string $fileExtension = 'txt'): void
    {
        $fileAdapter = new FileAdapter($this->outputFileName, $fileExtension);
        $anyString = print_r($anyData, true);
        $fileAdapter->storeData($anyString);
    }

    /**
     * @param mixed           $anyData
     * @param string          $fileExtension
     * @param string|string[] $dataHeader
     */
    protected function storeAsCsv(mixed $anyData, string $fileExtension = 'csv', string|array $dataHeader = []): void
    {
        $csvAdapter = new CsvFileAdapter($this->outputFileName, $fileExtension);
        $csvAdapter->storeDataHeader($dataHeader);
        $csvAdapter->storeData($anyData);
    }
}
