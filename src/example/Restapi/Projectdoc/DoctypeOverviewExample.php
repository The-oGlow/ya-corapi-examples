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

namespace oglowa\example\Restapi\Projectdoc;

use oglowa\example\Restapi\AbstractRestApiExample;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class DoctypeOverviewExample extends AbstractRestApiExample
{
    public const IDX_STRICT = 'strict';

    public const IDX_LAZY   = 'ids';

    public const IDX_SIZE   = 'size';

    public function getDoctype(int $foundPageId, string $propertyName): string
    {
        $this->logger->debug("START");

        $pdtResponse = $this->apiClient->pdtReadProperty($foundPageId, $propertyName);

        $foundDoctype = '';
        // FIXME: That is an response method -> PDTDEcorate
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
        $start      = 0;
        $limit      = 50;
        $maxCount   = 6; // PAGE_MAX_RESULTS * 0.1;
        $filterTerm = "type:page AND macroName:projectdoc-properties-marker";

        $doctypes[$spaceKey]    = [];
        $errDoctypes[$spaceKey] = [];

        $resultsCount = 0;
        $bLoop        = true;
        while ($bLoop) {
            $response = $this->apiClient->searchPagesWithFilter($filterTerm, $spaceKey, $start, $limit);

            if ($response->isAvailable()) {
                $propertyName = 'Doctype';

                foreach ($response->getResults() as $result) {
                    if (key_exists('content', $result)) {
                        $result = $result['content'];
                    }
                    $foundPageId = (int)$result['key'];

                    $foundDoctype = $this->getDoctype($foundPageId, $propertyName);
                    if (empty($foundDoctype)) {
                        $errDoctypes[$spaceKey][] = $foundPageId;
                    } else {
                        if (!key_exists($foundDoctype, $doctypes[$spaceKey])) {
                            $doctypes[$spaceKey][$foundDoctype][self::IDX_SIZE]   = 0;
                            $doctypes[$spaceKey][$foundDoctype][self::IDX_STRICT] = [];
                            $doctypes[$spaceKey][$foundDoctype][self::IDX_LAZY]   = [];
                        }
                        $doctypes[$spaceKey][$foundDoctype][self::IDX_SIZE]++;
                        if ($strictCheck && !$this->validateDoctypeName($foundDoctype)) {
                            $doctypes[$spaceKey][$foundDoctype][self::IDX_STRICT][] = $foundPageId;
                            $resultsCount++;
                        }

                        if (!$this->validateDoctypeName($foundDoctype, false)) {
                            $doctypes[$spaceKey][$foundDoctype][self::IDX_LAZY][] = $foundPageId;
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
                $allDoctypeData = array_unique(array_merge($doctypeData[self::IDX_STRICT], $doctypeData[self::IDX_LAZY]));
                $csvLine        = sprintf($format, $space, $doctype, $doctypeData[self::IDX_SIZE], implode(',', $allDoctypeData));
                $this->storeAsCsv($csvLine);
            }
        }
        $this->logger->debug("END");
    }
}

function main(): void
{
    $thisClazz = new DoctypeOverviewExample();
    $doctypes  = $thisClazz->loopPdtPages('NMVS', true);
    $thisClazz->storeDoctypes($doctypes);
}

main();
