<?php


namespace oglow\example\Restapi;

use oglow\tools\Yacorapi\IResponse;

require_once __DIR__ . '/../../bootstrap.php'; // NOSONAR: php:S4833

class GetSpaceHomepage extends AbstractRestApiExample
{
    public function getHomepage(string $spaceKey):void
    {
        $pageId = $this->apiClient->spaceHomepage($spaceKey);
        
        if ($pageId > IResponse::NO_PAGE_ID) {
            $this->logger->inofo('Hompage for space is',[$spaceKey,$pageId]);
            
        } else {
            $this->logger->warning('No homepage for space', [$spaceKey]);
        }
    }
   
}
function main(): void
{
    $thisClazz = new GetSpaceHomepage();

    $thisClazz->getHomepage('NMAS');
}

main();
