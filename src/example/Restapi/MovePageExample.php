<?php

declare(strict_types=1);

/*
 * This file is part of ya-corapi
 *
 * (c) 2024 Oliver Glowa, coding.glowa.com
 *
 * This source file is subject to the Apache-2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace oglowa\example\Restapi;

require_once __DIR__ . '/../bootstrap.php'; // NOSONAR: php:S4833

class MovePageExample extends AbstractRestApiExample
{
    public const        C_PLAYGROUND_ID = 532951146;

    public const        C_SPACE         = 'NMAS';

    public const        C_MOVE_TITLE    = 'MOVE PAGE %s-%s';

    public const        C_MOVE_BODY     = "Move page to playground";

    public function createPage(string $spaceKey, string $pageTitle, string $pageBody = '', ?int $parentId = null): int
    {
        $this->logger->debug("START spaceKey,pageTitle,parentId,empty(pageBody)", [$spaceKey, $pageTitle, $parentId, empty($pageBody)]);

        $response = $this->apiClient->createPage($spaceKey, $pageTitle, $pageBody, $parentId);
        $pageId   = $response->getValue('key', -1);

        $this->logger->debug("END pageId", [$pageId]);

        return intval($pageId);
    }

    public function movePage(int $pageId, int $newParentId): void
    {
        $this->logger->debug("START pageId,newParentId,spaceKey", [$pageId, $newParentId]);

        $response = $this->apiClient->movePage($pageId, $newParentId);
        $this->outputData($response);

        $this->logger->debug("END");
    }

    public function movePageInsideSpace(): void
    {
        $thisClazz = new MovePageExample();
        $title     = sprintf(MovePageExample::C_MOVE_TITLE, \oglowa\tools\Yacorapi\TS_NOW, 0);
        $pageId    = $thisClazz->createPage(MovePageExample::C_SPACE, $title, MovePageExample::C_MOVE_BODY);
        $thisClazz->movePage($pageId, MovePageExample::C_PLAYGROUND_ID);
    }
}

function main(): void
{
    $thisClazz = new MovePageExample();
    $thisClazz->movePageInsideSpace();
}

main();
