<?php

/*
 * Copyright 2026 GLO03.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace oglow\example;

use Psr\Log\LoggerInterface;
use Monolog\ConsoleLogger;
use Monolog\PlainLogger;
use ollily\Tools\String\ImplodeTrait;
use oglow\tools\Yacorapi\Store\CsvFileAdapter;
use oglow\tools\Yacorapi\Store\FileAdapter;
use Psr\Log\LogLevel;
use oglow\tools\Yacorapi\IResponse;

class AbstractExample {
    use ImplodeTrait;

    /** Default output level (DEBUG) */
    public const string LEVEL_DEFAULT = LogLevel::INFO;

    protected PlainLogger $output;
    private LoggerInterface $logger;

    private string $outputFileName;

    public function __construct(string $outputFileName = '') {
        $this->logger = new ConsoleLogger(get_class($this));
        $this->logger->debug("START");

        $this->outputFileName = empty($outputFileName) ? get_class($this) : $outputFileName;
        $this->output = new PlainLogger(get_class($this));

        $this->logger->debug("END");
    }
    
    protected function outputLine(string $line, ?int $idx = null): void {
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
    protected function outputData(mixed $anyData, ?int $idx = null): void {
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

    protected function outputDatas(IResponse $response): void {
        $idx = 0;
        /**
         * FIXME: IResponse liefert falschen Wert.
         *
         * @var IResponse|mixed[] $singleResult
         */
        foreach ($response->getResults() as $singleResult) {
            $this->outputData($singleResult, $idx++);
        }
    }

    protected function prepareTargetFileName(string $suffix = ''): string {
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
    protected function storeOrg(mixed $anyData, string $fileExtension = 'txt'): void {
        $fileAdapter = new FileAdapter($this->outputFileName, $fileExtension);
        $fileAdapter->storeData($anyData);
    }

    /**
     * @param mixed  $anyData
     * @param string $fileExtension
     */
    protected function storeMod(mixed $anyData, string $fileExtension = 'txt'): void {
        $fileAdapter = new FileAdapter($this->outputFileName, $fileExtension);
        $fileAdapter->storeData($anyData);
    }

    /**
     * @param mixed  $anyData
     * @param string $fileExtension
     */
    protected function storeAsDump(mixed $anyData, string $fileExtension = 'txt'): void {
        $fileAdapter = new FileAdapter($this->outputFileName, $fileExtension);
        $anyString = print_r($anyData, true);
        $fileAdapter->storeData($anyString);
    }

    /**
     * @param mixed           $anyData
     * @param string          $fileExtension
     * @param string|string[] $dataHeader
     */
    protected function storeAsCsv(mixed $anyData, string $fileExtension = 'csv', string|array $dataHeader = []): void {
        $csvAdapter = new CsvFileAdapter($this->outputFileName, $fileExtension);
        $csvAdapter->storeDataHeader($dataHeader);
        $csvAdapter->storeData($anyData);
    }
}
