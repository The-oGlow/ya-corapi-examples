<?php

declare(strict_types=1);

/*
 * This file is part of ya-corapi-examples
 *
 * (c) 2025 Oliver Glowa, coding.glowa.com
 *
 * This source file is subject to the Apache-2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace oglowa\example\Restapi;

use Monolog\ConsoleLogger;
use Monolog\PlainLogger;
use oglowa\tools\common\IStoreAdapter;
use oglowa\tools\Yacorapi\ConstData;
use oglowa\tools\Yacorapi\IResponse;
use oglowa\tools\Yacorapi\RapiClient;
use oglowa\tools\Yacorapi\Store\CsvFileAdapter;
use oglowa\tools\Yacorapi\Store\FileAdapter;
use ollily\Tools\String\ImplodeTrait;
use Psr\Log\LoggerInterface;

abstract class AbstractRestApiExample
{
    use ImplodeTrait;

    /** @var LoggerInterface */
    protected $logger;

    /** @var PlainLogger */
    protected $output;

    /** @var RapiClient */
    protected $apiClient;

    /** @var IStoreAdapter */
    protected $storeAdapter;

    /** @var string */
    private $outputFileName;

    public function __construct(string $outputFileName = '')
    {
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
    protected function outputData($anyData, ?int $idx = null): void
    {
        $prefix = '';
        if (isset($idx)) {
            $prefix = sprintf("%s;", $idx);
        }
        if (is_a($anyData, IResponse::class)) {
            $this->output->out($prefix . $anyData);
        } else {
            $this->output->out($prefix . $this->implode_recursive(",", $anyData, false, true));
        }
    }

    protected function outputDatas(IResponse $response): void
    {
        $idx = 0;
        /** @var mixed[] $singleResult */
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
     * @param IResponse $response
     * @param null|int  $idx
     */
    protected function showResults(IResponse $response, ?int $idx = null): void
    {
        $line = [];

        if (isset($idx)) {
            $line[] = $idx;
        }
        /** @var mixed */
        $content = $response->getValue(IResponse::KEY_CONTENT);
        if (!empty($content) && is_array($content)) {
            array_push(
                $line,
                [
                    $content[IResponse::KEY_ID] ?? $response->getValue(IResponse::KEY_ID),
                    $content[IResponse::KEY_SPACE][IResponse::KEY_KEY] ?? $response->getValue(IResponse::KEY_ID)[IResponse::KEY_KEY],
                    $content[IResponse::KEY_TITLE] ?? $response->getValue(IResponse::KEY_TITLE),
                    $content[IResponse::KEY_TYPE] ?? $response->getValue(IResponse::KEY_TYPE),
                    $response->getValue(IResponse::KEY_LINKS)[IResponse::KEY_BASE] ?? ConstData::c(ConstData::C_CONF_BASE_URL),
                    $content[IResponse::KEY_LINKS][IResponse::KEY_WEBUI] ?? $response->getValue(
                        IResponse::KEY_URL,
                        $response->getValue(IResponse::KEY_LINKS)
                        [IResponse::KEY_WEBUI]
                    ),
                ]
            );
        } else {
            $this->logger->warning('content is empty or no array', [gettype($content)]);
        }
        $this->logger->info("$idx.", [$line]);
    }

    /**
     * @param IResponse $response
     */
    protected function showTotals(IResponse $response): void
    {
        $start = $response->getValue(IResponse::KEY_START, '-');
        $size  = $response->getValue(IResponse::KEY_SIZE, '-');
        $limit = $response->getValue(IResponse::KEY_LIMIT, '-');
        $total = $response->getValue(IResponse::KEY_TOTAL, $response->getValue(IResponse::KEY_TOTAL_SIZE, '-'));

        $this->logger->info("Total,Start,Size,Limit", [$total, $start, $size, $limit]);
    }

    /**
     * @param mixed       $anyData
     * @param null|string $fileExtension
     */
    protected function storeOrg($anyData, ?string $fileExtension = 'txt'): void
    {
        // REFACTOR: Die Angabe von Pfad und Dateiname ist unsauber
        $targetFile = $this->storeAdapter->prepareTargetFileParam(
            $this->prepareTargetPathName(ConstData::c(ConstData::C_TARGET_ORGDIR_PH)),
            $this->prepareTargetFileName('org'),
            $fileExtension ?? 'txt'
        );
        $this->storeAdapter->storeData($targetFile, $anyData);
    }

    /**
     * @param mixed       $anyData
     * @param null|string $fileExtension
     */
    protected function storeMod($anyData, ?string $fileExtension = 'txt'): void
    {
        // REFACTOR: Die Angabe von Pfad und Dateiname ist unsauber
        $targetFile = $this->storeAdapter->prepareTargetFileParam(
            $this->prepareTargetPathName(ConstData::c(ConstData::C_TARGET_MODDIR_PH)),
            $this->prepareTargetFileName('mod'),
            $fileExtension ?? 'txt'
        );
        $this->storeAdapter->storeData($targetFile, $anyData);
    }

    /**
     * @param mixed       $anyData
     * @param null|string $fileExtension
     */
    protected function storeAsDump($anyData, ?string $fileExtension = 'txt'): void
    {
        $anyString = print_r($anyData, true);

        // REFACTOR: Die Angabe von Pfad und Dateiname ist unsauber
        $targetFile = $this->storeAdapter->prepareTargetFileParam(
            $this->prepareTargetPathName(ConstData::c(ConstData::C_TARGET_DIR_PH)),
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
    protected function storeAsCsv($anyData, ?string $fileExtension = 'csv', $dataHeader = []): void
    {
        /** @var CsvFileAdapter */
        $csvAdapter = new CsvFileAdapter($this->outputFileName);

        // REFACTOR: Die Angabe von Pfad und Dateiname ist unsauber
        $targetFile = $csvAdapter->prepareTargetFileParam(
            $this->prepareTargetPathName(ConstData::c(ConstData::C_TARGET_DIR_PH)),
            $this->prepareTargetFileName(),
            $fileExtension ?? 'csv'
        );

        $csvAdapter->storeDataHeader($targetFile, $dataHeader);
        $csvAdapter->storeData($targetFile, $anyData);
    }
}
