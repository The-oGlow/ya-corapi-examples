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
use oglow\tools\Yacorapi\Response\ResponseParameter;
use oglow\tools\Yacorapi\Store\CsvFileAdapter;
use oglow\tools\Yacorapi\Store\FileAdapter;
use oglow\tools\Yacorapi\Store\FileStoreStageEnum;
use oglow\tools\Yacorapi\Store\StoreParameter;
use ollily\Tools\String\ImplodeTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @author ollily
 */
class AbstractExample
{
    use ImplodeTrait;

    /** Default output level*/
    protected const string LEVEL_DEFAULT = LogLevel::INFO;

    /** Writes text as it is to console. */
    protected PlainLogger $output;

    private LoggerInterface $logger;

    /** The outputfile incl. path and file extension or the class-string of the caller class. */
    private string $outputFileName;

    /**
     * @param string $outputFileName The outputfile incl. path and file extension
     */
    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(AbstractExample::class);
        $this->logger->debug('START');

        $this->outputFileName = empty($outputFileName) ? get_class($this) : $outputFileName;
        $this->output = new PlainLogger(get_class($this));

        $this->logger->debug('END');
    }

    /**
     * Writes a text into {@link AbstractExample->$output}.
     *
     * @param string   $line The text for the output
     * @param null|int $idx  A counter (Default: null)
     */
    protected function outputLine(string $line, ?int $idx = null): void
    {
        $prefix = '';
        if (isset($idx)) {
            $prefix = sprintf('%s;', $idx);
        }
        $this->output->out($prefix . $line);
    }

    /**
     * Writes everything you want into {@link AbstractExample->$output}.
     *
     * @param mixed    $anyData Everything you want to output
     * @param null|int $idx     A counter (Default: null)
     */
    protected function outputData(mixed $anyData, ?int $idx = null): void
    {
        $prefix = '';
        if (isset($idx)) {
            $prefix = sprintf('%s;', $idx);
        }
        if (is_a($anyData, IResponse::class)) {
            $this->output->out($prefix .  "$anyData");
        } else {
            $this->output->out($prefix . self::implode_recursive(',', $anyData, false, true));
        }
    }

    /**
     * Writes a response from a REST-API call into  {@link AbstractExample->$output}.
     *
     * @param null|IResponse $response The response from the REST-API call
     */
    protected function outputDatas(?IResponse $response): void
    {
        $idx = 1;
        // FIXME: IResponse liefert falschen Wert.
        if (!empty($response)) {
            if ($response->getResults()->count() > 0) {
                foreach ($response->getResults() as $singleResult) {
                    if ($singleResult instanceof IResponse) {
                        $this->outputData($singleResult->getValue(ResponseParameter::KEY_ID), $idx++);
                    } else {
                        $this->outputData([$singleResult[ResponseParameter::KEY_ID],
                            $singleResult[ResponseParameter::KEY_SPACE][ResponseParameter::KEY_KEY], $singleResult[ResponseParameter::KEY_TITLE]], $idx++);
                    }
                }
            } else {
                $this->output->out('Empty results');
            }
        } else {
            $this->output->out('Empty response');
        }
    }

    /**
     * Stores everything you give into a file at stage {@link FileStoreStageEnum::ORIGINAL}.
     *
     * @param mixed  $anyData       Everything you want to store
     * @param string $fileExtension The file extension for the output file (Default: {@link StoreParameter::C_FILE_EXT_TEXT})
     */
    protected function storeOrg(mixed $anyData, string $fileExtension = StoreParameter::C_FILE_EXT_TEXT): void
    {
        $fileAdapter = new FileAdapter($this->outputFileName, $fileExtension, staging: FileStoreStageEnum::ORIGINAL);
        $fileAdapter->storeData($anyData);
    }

    /**
     * Stores everything you give into a file at stage {@link FileStoreStageEnum::MODIFIED}.
     *
     * @param mixed  $anyData       Everything you want to store
     * @param string $fileExtension The file extension for the output file (Default: {@link StoreParameter::C_FILE_EXT_TEXT})
     */
    protected function storeMod(mixed $anyData, string $fileExtension = StoreParameter::C_FILE_EXT_TEXT): void
    {
        $fileAdapter = new FileAdapter($this->outputFileName, $fileExtension, staging: FileStoreStageEnum::MODIFIED);
        $fileAdapter->storeData($anyData);
    }

    /**
     * Stores everything you give into a file as a dump.
     *
     * @param mixed  $anyData       Everything you want to store
     * @param string $fileExtension The file extension for the output file (Default: {@link StoreParameter::C_FILE_EXT_TEXT})
     */
    protected function storeAsDump(mixed $anyData, string $fileExtension = StoreParameter::C_FILE_EXT_TEXT): void
    {
        $fileAdapter = new FileAdapter($this->outputFileName, $fileExtension);
        $fileAdapter->storeData(print_r($anyData, true));
    }

    /**
     * Stores everything you give into a file with csv format.
     *
     * @param mixed                     $anyData       Everything you want to store
     * @param string                    $fileExtension The file extension for the output file (Default: {@link StoreParameter::C_FILE_EXT_CSV})
     * @param array<mixed,mixed>|string $dataHeader    A header, which will be set at the top of the file (Default: [])
     */
    protected function storeAsCsv(mixed $anyData, string $fileExtension = StoreParameter::C_FILE_EXT_CSV, string|array $dataHeader = []): void
    {
        $csvAdapter = new CsvFileAdapter($this->outputFileName, $fileExtension);
        $csvAdapter->storeDataHeader($dataHeader);
        $csvAdapter->storeData($anyData);
    }
}
