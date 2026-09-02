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

use Ds\Map;
use Monolog\ConsoleLogger;
use oglow\example\AbstractRestApiExample;
use oglow\tools\Yacorapi\Client\IRapiClientBase;
use oglow\tools\Yacorapi\ConstData;
use oglow\tools\Yacorapi\Helper\ContentHelper;
use oglow\tools\Yacorapi\Response\Response;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class UpdatePageExample extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug('START');

        parent::__construct($outputFileName);

        $this->logger->debug('END');
    }

    public function updatePage(
        string $spaceKey,
        int $pageId,
        string $pageTitle,
        string $pageBody,
        string $newPageTitle = IRapiClientBase::REQ_VAL_PAGE_TITLE_EMPTY,
        string $newPageBody = IRapiClientBase::REQ_VAL_BODY_EMPTY
    ): void {
        if (IRapiClientBase::REQ_VAL_PAGE_ID_NO !== $pageId) {
            switch (true) {
                case (!empty($newPageTitle) && !empty($newPageBody)):
                    $result = $this->apiClient->updatePage($pageId, pageTitle: $newPageTitle, pageBody: $newPageBody);
                    break;
                case !empty($newPageBody):
                    $result = $this->apiClient->updatePage($pageId, pageBody: $newPageBody);
                    break;
                case !empty($newPageTitle):
                    $result = $this->apiClient->updatePage($pageId, pageTitle: $newPageTitle);
                    break;
                default:
                    $result = new Response();
            }
            if ($result->checkStatus()) {
                $this->logger->info('Page updated with', [$spaceKey, $pageTitle, $pageId, $newPageTitle, strlen($pageBody), strlen($newPageBody)]);
            }
        } else {
            $this->logger->warning('Page not exists', [$spaceKey, $pageTitle, $pageId]);
        }
    }
}

function main(): void
{
    /** Space */
    $spaceKey = 'CMMN';

    $pageTitle = sprintf('%s %s-%s', 'NEW PAGE TO UPDATE', ConstData::getTsNow(), 0);
    $newPageTitle = sprintf('%s-%s', $pageTitle, 1);
    $finalPageTitle = sprintf('%s-%s', $newPageTitle, 2);

    $pageBody = ContentHelper::prepareMacro('projectdoc-iteration', new Map(['value' => 'facade']));
    $newPageBody = ContentHelper::prepareMacro('projectdoc-iteration', new Map(['value' => 'finished']));
    $finalPageBody = ContentHelper::prepareMacro('projectdoc-iteration', new Map(['value' => 'production']));

    $thisClazz = new UpdatePageExample();

    $pageId = $thisClazz->createExamplePage($spaceKey, $pageTitle, $pageBody);

    // Update page body
    $thisClazz->updatePage($spaceKey, $pageId, $pageTitle, $pageBody, newPageBody: $newPageBody);

    // Update page title
    $thisClazz->updatePage($spaceKey, $pageId, $pageTitle, $pageBody, newPageTitle: $newPageTitle);

    // Update page title and body
    $thisClazz->updatePage($spaceKey, $pageId, $pageTitle, $pageBody, $finalPageTitle, $finalPageBody);
}

main();
