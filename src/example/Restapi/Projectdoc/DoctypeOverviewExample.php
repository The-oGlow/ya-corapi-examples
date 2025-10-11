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

namespace oglowa\example\Restapi\Projectdoc;

use Ds\Map;
use oglowa\example\Restapi\AbstractRestApiExample;
use oglowa\tools\Yacorapi\ConstData;
use oglowa\tools\Yacorapi\IResponse;
use oglowa\tools\Yacorapi\Projectdoc\TraitProjectdoc;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class DoctypeOverviewExample extends AbstractRestApiExample
{
    use TraitProjectdoc;

    public const DOCTYPE_STRICT = 'strict';

    public const DOCTYPE_LAZY = 'ids';

    public const DOCTYPE_TOTALS = 'size';

    public function getDoctype(int $foundPageId, string $propertyName): string
    {
        $this->logger->debug("START");

        $pdtResponse = $this->apiClient->pdtReadProperty($foundPageId, $propertyName);

        $foundDoctype = '';
        // REFACTOR: That is an response method -> PDTDEcorate
        if ($this->apiClient->checkDataPdtProperty($pdtResponse)) {
            $foundDoctype = $pdtResponse->getValue('value');
        } else {
            $this->logger->debug("No property found in page", [$propertyName, $foundPageId]);
        }

        $this->logger->debug("END");

        return $foundDoctype;
    }

    public function loopPdtPages(string $spaceKey, bool $strictCheck = false): array
    {
        $this->logger->debug("START");
        $start      = ConstData::PAGE_START;
        $limit      = ConstData::PAGE_LIMIT;
        $maxCount   = ConstData::PAGE_MAX_RESULTS * 0.1;
        $filterTerm = "type:page AND macroName:projectdoc-properties-marker";

        $doctypes = [];
        $doctypes[$spaceKey]    = [];
        $errDoctypes = [];
        $errDoctypes[$spaceKey] = [];

        $resultsCount = 0;
        $bLoop        = true;
        while ($bLoop) {
            /** @var IResponse */
            $response = $this->apiClient->searchPagesWithFilter($filterTerm, $spaceKey, $start, $limit);

            if ($response->isResultsAvailable()) {
                $propertyName =  \oglowa\tools\Yacorapi\Projectdoc\PDT_PROP_DOCTYPE;
                /** @var Map<mixed,mixed> $results */
                $results = $response->getResults();
                if ($results->hasKey(IResponse::KEY_CONTENT)) {
                    $results = $results->get(IResponse::KEY_CONTENT);
                }

                foreach ($results as $resultValue) {
                    $foundPageId = (int)$resultValue[IResponse::KEY_KEY];

                    $foundDoctype = $this->getDoctype($foundPageId, $propertyName);
                    if (empty($foundDoctype)) {
                        $errDoctypes[$spaceKey][] = $foundPageId;
                    } else {
                        if (!key_exists($foundDoctype, $doctypes[$spaceKey])) {
                            $doctypes[$spaceKey][$foundDoctype][self::DOCTYPE_TOTALS] = 0;
                            $doctypes[$spaceKey][$foundDoctype][self::DOCTYPE_STRICT] = [];
                            $doctypes[$spaceKey][$foundDoctype][self::DOCTYPE_LAZY]   = [];
                        }
                        $doctypes[$spaceKey][$foundDoctype][self::DOCTYPE_TOTALS]++;
                        if ($strictCheck && !$this->validateDoctypeName($foundDoctype)) {
                            $doctypes[$spaceKey][$foundDoctype][self::DOCTYPE_STRICT][] = $foundPageId;
                            $resultsCount++;
                        }

                        if (!$this->validateDoctypeName($foundDoctype, false)) {
                            $doctypes[$spaceKey][$foundDoctype][self::DOCTYPE_LAZY][] = $foundPageId;
                            $resultsCount++;
                        }
                    }
                }
            } else {
                $this->logger->debug("Exiting no results found.");
                $bLoop = false;
                break;
            }
            $this->logger->debug("Found invalid doctypes (max)", [$resultsCount, $maxCount]);
            if ($resultsCount > $maxCount) {
                $this->logger->debug("Exiting after at least invalid doctypes", [$maxCount]);
                $bLoop = false;
                break;
            }
            $start += $limit;
        }
        $this->logger->debug("Found pages without doctype", [$errDoctypes]);
        $this->logger->debug("END");

        return $doctypes;
    }

    private function validateDoctypeName(string $doctype, bool $strict = true): bool
    {
        $this->logger->debug("START");
        $valid = false;

        $matchDoctype = '/^[a-z\-]{1,}$/';
        if (!$strict) {
            $matchDoctype = '/^[a-zA-Z0-9\-\_]{1,}$/';
        }
        if (preg_match($matchDoctype, $doctype)) {
            $valid = true;
        }

        $this->logger->debug("END");

        return $valid;
    }

    public function storeDoctypes(array $docTypes): void
    {
        $this->logger->debug("START");

        $format = "\"%s\";\"%s\";%s;%s";
        $this->storeAsCsv(null, null, ['Space', 'Doctype', 'Size', 'Page ID']);
        foreach ($docTypes as $space => $spaceDoctypes) {
            foreach ($spaceDoctypes as $doctype => $doctypeData) {
                $allDoctypeData = array_unique(array_merge($doctypeData[self::DOCTYPE_STRICT], $doctypeData[self::DOCTYPE_LAZY]));
                $csvLine        = sprintf($format, $space, $doctype, $doctypeData[self::DOCTYPE_TOTALS], implode(',', $allDoctypeData));
                $this->storeAsCsv($csvLine);
            }
        }
        $this->logger->debug("END");
    }
}

function main(): void
{
    $spaceKey  = 'NMVS';

    $thisClazz = new DoctypeOverviewExample();
    $doctypes  = $thisClazz->loopPdtPages($spaceKey, true);
    $thisClazz->storeDoctypes($doctypes);
}

main();
