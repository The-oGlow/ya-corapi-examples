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

use Ds\Map;
use Monolog\ConsoleLogger;
use oglow\example\ExampleErrorCodesEnum;
use oglow\tools\Yacorapi\Helper\ContentHelper;
use ollily\Tools\Batch\BatchConfig;
use ollily\Tools\Batch\BatchTaskHelper;
use ollily\Tools\Batch\ITaskItem;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class UpdatePageExample extends AbstractRestApiExample
{
    public const int MAX_ITERATION = 10;

    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);

        $this->logger->debug("END");
    }

    private function loopThruUpdates(ITaskItem $task): void
    {
        /** @var int $pageId */
        $pageId = intval($task->getData()[0]);
        $response = $this->apiClient->readPageByPageId($pageId);

        if ($response->isResultsAvailable()) {
            $pageIdLoaded = $response->getValue('key');
            if ($pageId == $pageIdLoaded) {
                $pageTitleLoaded   = $response->getValue('title');
                $pageVersionLoaded = $response->getValue('version')['number'];
                $pageBodyLoaded    = $response->getValue('body')['storage']['value'];

                $suffix = $pageIdLoaded . '-' . str_replace(' ', '_', substr($pageTitleLoaded, 0, 15)) . ".xml";
                $this->storeOrg($pageBodyLoaded, $suffix);
                $pageBodyModified = ContentHelper::prepareMacro('info', new Map(['title' => 'Modified Content']), 'I changed this page1');
                $this->storeMod($pageBodyModified, $suffix);
                $this->apiClient->updatePage($pageIdLoaded, $pageBodyModified, $pageVersionLoaded, $pageTitleLoaded);
            } else {
                $this->logger->error("+++ Not matching key of requested page and loaded page", [$pageId, $pageIdLoaded]);
            }
        } else {
            $this->logger->warning("Requested page not loaded", [$pageId]);
        }
    }

    /**
     * @SuppressWarnings("PHPMD.ExitExpression")
     */
    public function mainUpdate(): void
    {
        $key      = "tasks-pageid";
        $fileName = "";
        $batchConfig = new BatchConfig(new Map());

        $tasklist = BatchTaskHelper::readTaskList($fileName, $batchConfig, $key);

        $fallbackIdx = 0;
        while (!$tasklist->isEmpty()) {
            $task = $tasklist->nextTask();
            $this->logger->debug("task:", [$fallbackIdx, $task]);
            if (!is_null($task)) {
                $this->loopThruUpdates($task);
                $fallbackIdx++;
                if ($fallbackIdx >= self::MAX_ITERATION) {
                    $this->logger->warning("+++ fallback exit after iterations +++", [$fallbackIdx]);

                    die(ExampleErrorCodesEnum::ERR_MAX_ITERATION->intValue()); // NOSONAR:php:S1799
                }
            }
        }
    }
}

function main(): void
{
    $thisClazz = new UpdatePageExample();
    $thisClazz->mainUpdate();
}

main();
