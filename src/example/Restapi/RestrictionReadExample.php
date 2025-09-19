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

require_once __DIR__ . '/../bootstrap.php'; // NOSONAR: php:S4833

/** @var int $pageId */
$pageId = 608567375; // 591855803;521933587;

class RestrictionReadExample extends AbstractRestApiExample
{
    public function readRestrictionByPageId(int $pageId): void
    {
        $this->logger->debug("START", [$pageId]);
        $response = $this->apiClient->readRestrictionsByPageId($pageId);
        $this->outputData($response);
        $this->logger->debug("END");
    }
}

function main(): void
{
    global $pageId, $searchTerm, $spaceKey;
    $thisClazz = new RestrictionReadExample();

    $thisClazz->readRestrictionByPageId($pageId);
}

main();
