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

namespace oglow\example\read;

use Monolog\ConsoleLogger;
use oglow\example\AbstractRestApiExample;
use oglow\tools\Yacorapi\IResponse;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class GetSpaceHomepage extends AbstractRestApiExample
{
    private LoggerInterface $logger;

    public function __construct(string $outputFileName = '')
    {
        $this->logger = new ConsoleLogger(get_class($this));

        $this->logger->debug("START");
        parent::__construct($outputFileName);

        $this->logger->debug("END");
    }

    public function getHomepage(string $spaceKey): void
    {
        $pageId = $this->apiClient->spaceHomepage($spaceKey);

        if ($pageId > IResponse::NO_PAGE_ID) {
            $this->logger->info('Hompage for space is', [$spaceKey,$pageId]);
        } else {
            $this->logger->warning('No homepage for space', [$spaceKey]);
        }
    }
}
function main(): void
{
    /*     * space */
    $spaceKey = 'CMMN';

    $thisClazz = new GetSpaceHomepage();

    $thisClazz->getHomepage($spaceKey);
}

main();
