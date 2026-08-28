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

use Psr\Log\LoggerInterface;
use Monolog\ConsoleLogger;
use oglow\tools\Yacorapi\Client\IRapiClientBase;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

use oglow\tools\Yacorapi\Data\RequestParameterData;

class MovePageExample extends AbstractRestApiExample
{
    public const  int      C_PLAYGROUND_ID = 532951146;

    public const string C_SPACE         = 'NMAS';

    public const string C_MOVE_TITLE    = 'MOVE PAGE %s-%s';

    public const string C_MOVE_BODY     = "Move page to playground";

    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '') {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);

        $this->logger->debug("END");
    }

    public function createPage(string $spaceKey, string $pageTitle, string $pageBody = '', int $parentId = IRapiClientBase::REQ_NO_PARENT,): int
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
        $title     = sprintf(MovePageExample::C_MOVE_TITLE, \oglow\tools\Yacorapi\ConstData::getTsNow(), 0);
        $pageId    = $thisClazz->createPage(MovePageExample::C_SPACE, $title, MovePageExample::C_MOVE_BODY, RequestParameterData::NO_PARENT);
        $thisClazz->movePage($pageId, MovePageExample::C_PLAYGROUND_ID);
    }
}

function main(): void
{
    $thisClazz = new MovePageExample();
    $thisClazz->movePageInsideSpace();
}

main();
