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

namespace oglow\example\Restapi;

use Monolog\ConsoleLogger;
use Monolog\PlainLogger;
use oglow\tools\common\IStoreAdapter;
use oglow\tools\Yacorapi\ConstData;
use oglow\tools\Yacorapi\Impl\CsvFileAdapter;
use oglow\tools\Yacorapi\Impl\FileAdapter;
use oglow\tools\Yacorapi\IResponse;
use oglow\tools\Yacorapi\RapiClient;
use ollily\Tools\String\ImplodeTrait;
use Psr\Log\LoggerInterface;

abstract class AbstractRestApiExample
{
    use ImplodeTrait;

    /** @var LoggerInterface */
    protected LoggerInterface $logger;

    /** @var PlainLogger */
    protected PlainLogger $output;

    /** @var RapiClient */
    protected RapiClient $apiClient;

    /** @var IStoreAdapter */
    protected IStoreAdapter $storeAdapter;

    /** @var string */
    private string $outputFileName;

    public function __construct(string $outputFileName = '')
    {
        ConstData::instance();
        $this->logger = new ConsoleLogger(get_class($this));
        $this->logger->debug("START");

        $this->outputFileName = empty($outputFileName) ? get_class($this) : $outputFileName;
        $this->output         = new PlainLogger(get_class($this));
        $this->storeAdapter   = new FileAdapter($this->outputFileName);
        $this->apiClient      = new RapiClient();

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
            $this->output->out($prefix . $anyData);
        } else {
            $this->output->out($prefix . $this->arrayRecImplode(",", $anyData, false, true));
        }
    }

    protected function outputDatas(IResponse $response): void
    {
        $idx = 0;
        /**
         * FIXME: IResponse liefert falschen Wert.
         *
         * @var IResponse|mixed[] $singleResult
         *
         * @psalm-suppress PossibleRawObjectIteration
         * @phpstan-ignore foreach.nonIterable
         */
        foreach ($response->getResults() as $singleResult) {
            $this->outputData($singleResult, $idx++);
        }
    }

    protected function prepareTargetPathName(string $pathPH): string
    {
        return ConstData::realScriptName($pathPH, $this->outputFileName);
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
     * @param mixed       $anyData
     * @param null|string $fileExtension
     */
    protected function storeOrg(mixed $anyData, ?string $fileExtension = 'txt'): void
    {
        // FIXME: Die Angabe von Pfad und Dateiname ist unsauber
        $targetFile = $this->storeAdapter->prepareTargetFileParam(
            $this->prepareTargetPathName(\oglow\tools\Yacorapi\TARGET_ORGDIR),
            $this->prepareTargetFileName('org'),
            $fileExtension ?? 'txt'
        );
        $this->storeAdapter->storeData($targetFile, $anyData);
    }

    /**
     * @param mixed       $anyData
     * @param null|string $fileExtension
     */
    protected function storeMod(mixed $anyData, ?string $fileExtension = 'txt'): void
    {
        // FIXME: Die Angabe von Pfad und Dateiname ist unsauber
        $targetFile = $this->storeAdapter->prepareTargetFileParam(
            $this->prepareTargetPathName(\oglow\tools\Yacorapi\TARGET_MODDIR),
            $this->prepareTargetFileName('mod'),
            $fileExtension ?? 'txt'
        );
        $this->storeAdapter->storeData($targetFile, $anyData);
    }

    /**
     * @param mixed       $anyData
     * @param null|string $fileExtension
     */
    protected function storeAsDump(mixed $anyData, ?string $fileExtension = 'txt'): void
    {
        $anyString = print_r($anyData, true);

        // FIXME: Die Angabe von Pfad und Dateiname ist unsauber
        $targetFile = $this->storeAdapter->prepareTargetFileParam(
            $this->prepareTargetPathName(\oglow\tools\Yacorapi\TARGET_DIR),
            $this->prepareTargetFileName('dump'),
            $fileExtension ?? 'txt'
        );
        $this->storeAdapter->storeData($targetFile, $anyString);
    }

    /**
     * @param mixed           $anyData
     * @param null|string     $fileExtension
     * @param string|string[] $dataHeader
     */
    protected function storeAsCsv(mixed $anyData, ?string $fileExtension = 'csv', string|array $dataHeader = []): void
    {
        /** @var CsvFileAdapter */
        $csvAdapter = new CsvFileAdapter($this->outputFileName);

        // FIXME: Die Angabe von Pfad und Dateiname ist unsauber
        $targetFile = $csvAdapter->prepareTargetFileParam(
            $this->prepareTargetPathName(\oglow\tools\Yacorapi\TARGET_DIR),
            $this->prepareTargetFileName(),
            $fileExtension ?? 'csv'
        );

        $csvAdapter->storeDataHeader($targetFile, $dataHeader);
        $csvAdapter->storeData($targetFile, $anyData);
    }
}
