<?php

declare(strict_types=1);

/*
 * This file is part of ya-corapi-examles
 *
 * (c) 2024 Oliver Glowa, coding.glowa.com
 *
 * This source file is subject to the Apache-2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace oglowa\example\Restapi;

use oglowa\tools\Yacorapi\Helper\BatchTaskHelper;
use oglowa\tools\Yacorapi\Helper\ContentHelper;
use oglowa\tools\Yacorapi\Helper\Task;
use oglowa\tools\Yacorapi\IResponse;

require_once __DIR__ . '/../bootstrap.php'; // NOSONAR: php:S4833

/**
 * FIXME:Remove.
 *
 * @SuppressWarnings(PHPMD)
 */
class UpdatePageExample extends AbstractRestApiExample
{
    private function loopThruUpdates(Task $task): void
    {
        /** @var int $pageId */
        $pageId = intval($task->getData()[0]);
        /** @var IResponse $response */
        $response = $this->apiClient->readPageByPageId($pageId);

        if ($response->isAvailable()) {
            $pageIdLoaded = $response->getValue('key');
            if ($pageId == $pageIdLoaded) {
                $pageTitleLoaded   = $response->getValue('title');
                $pageVersionLoaded = $response->getValue('version')['number'];
                $pageBodyLoaded    = $response->getValue('body')['storage']['value'];

                $suffix = $pageIdLoaded . '-' . str_replace(' ', '_', substr($pageTitleLoaded, 0, 15)) . ".xml";
                $this->storeOrg($pageBodyLoaded, $suffix);
                $pageBodyModified = ContentHelper::getI()->prepareMacro('info', ['title' => 'Modified Content'], 'I changed this page1');
                $this->storeMod($pageBodyModified, $suffix);
                $this->apiClient->updatePage($pageIdLoaded, $pageBodyModified, $pageVersionLoaded, $pageTitleLoaded);
            } else {
                $this->logger->error("+++ Not matching key of requested page and loaded page", [$pageId, $pageIdLoaded]);
            }
        } else {
            $this->logger->warning("Requested page not loaded", [$pageId]);
        }
    }

    public function mainUpdate(): void
    {
        $key      = "tasks-pageid";
        $tasklist = BatchTaskHelper::getI()->readResultFile($key);

        $fallbackIdx = 0;
        foreach ($tasklist as $task) {
            $this->logger->debug("task:", [$fallbackIdx, $task]);
            $this->loopThruUpdates($task);
            $fallbackIdx++;
            if ($fallbackIdx >= 10) {
                $this->logger->warning("+++ fallback exit after iterations +++", [$fallbackIdx]);

                exit(10);
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
