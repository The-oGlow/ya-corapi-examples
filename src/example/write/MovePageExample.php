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

namespace oglow\example\write;

use Monolog\ConsoleLogger;
use oglow\example\AbstractRestApiExample;
use oglow\tools\Yacorapi\Client\IRapiClientBase;
use oglow\tools\Yacorapi\ConstData;
use oglow\tools\Yacorapi\IResponse;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class MovePageExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug('START');
        parent::__construct($outputFileName);

        $this->logger->debug('END');
    }

    public function movePage(int $pageId, int $newParentId): int
    {
        $this->logger->info('START pageId,newParentId', [$pageId, $newParentId]);

        $movedParentPageId = IRapiClientBase::RESP_VAL_PAGE_ID_NO;

        $result = $this->apiClient->movePage($pageId, $newParentId);
        if ($result->checkStatus()) {
            $movedParentPageId = (int) $result->getValue(IResponse::KEY_ID);
            $this->logger->info('Page moved to', [$pageId, $movedParentPageId]);
        } else {
            $this->logger->error('Page not moved', [$result->getError()]);
        }

        $this->logger->info('END');

        return $movedParentPageId;
    }

    public function movePageInsideSpace(string $spaceKey, string $pageTitle, string $pageBody, string $newParentPageTitle): void
    {
        $newParentPageId = $this->apiClient->checkPageExists($spaceKey, $newParentPageTitle);

        if (IRapiClientBase::RESP_VAL_PAGE_ID_NO !== $newParentPageId) {
            $spaceRootPageId = $this->apiClient->spaceHomepage($spaceKey);

            if (IRapiClientBase::RESP_VAL_PAGE_ID_NO !== $spaceRootPageId) {
                $pageId = $this->createOrUpdateExamplePage($spaceKey, $pageTitle, $pageBody, $spaceRootPageId);

                if (IRapiClientBase::RESP_VAL_PAGE_ID_NO !== $pageId) {
                    $movedParentPageId = $this->movePage($pageId, $newParentPageId);

                    if (IRapiClientBase::RESP_VAL_PAGE_ID_NO !== $movedParentPageId) {
                        $this->logger->info('Page created and moved', [$spaceKey, $spaceRootPageId, $pageTitle, $pageId, $newParentPageId, $movedParentPageId]);
                    } else {
                        $this->logger->warning('Page created but not moved', [$spaceKey, $spaceRootPageId, $pageTitle, $pageId, $newParentPageId]);
                    }
                } else {
                    $this->logger->warning('Page not created', [$spaceKey, $spaceRootPageId, $pageTitle, $pageId, $newParentPageId]);
                }
            } else {
                $this->logger->critical('Space Homepage does not exist', [$spaceKey, $spaceRootPageId, $newParentPageTitle, $newParentPageId]);
            }
        } else {
            $this->logger->critical('New parent page does not exist', [$spaceKey, $newParentPageTitle, $newParentPageId]);
        }
    }
}

function main(): void
{
    /** Space */
    $spaceKey = 'CMMN';

    $pageTitle = sprintf('%s %s-%s', 'NEW PAGE TO MOVE', ConstData::getTsNow(), 0);

    $pageBody = 'Page created and moved to playground';

    /** Parent page title */
    $newParentPageTitle = '98-Playground';

    $thisClazz = new MovePageExample();
    $thisClazz->movePageInsideSpace($spaceKey, $pageTitle, $pageBody, $newParentPageTitle);
}

main();
