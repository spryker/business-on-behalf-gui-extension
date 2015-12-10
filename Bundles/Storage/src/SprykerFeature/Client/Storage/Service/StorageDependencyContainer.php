<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Client\Storage\Service;

use SprykerFeature\Client\Storage\Service\Redis\Service;
use Predis\Client;
use SprykerEngine\Client\Kernel\Service\AbstractServiceDependencyContainer;
use SprykerFeature\Shared\Library\Config;
use SprykerFeature\Shared\Application\ApplicationConfig;

class StorageDependencyContainer extends AbstractServiceDependencyContainer
{

    /**
     * @return StorageClientInterface
     */
    public function createService()
    {
        return new Service(
            $this->createClient()
        );
    }

    protected function createClient()
    {
        return new Client($this->getConfig());
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    private function getConfig()
    {
        return [
            'protocol' => Config::get(ApplicationConfig::YVES_STORAGE_SESSION_REDIS_PROTOCOL),
            'port' => Config::get(ApplicationConfig::YVES_STORAGE_SESSION_REDIS_PORT),
            'host' => Config::get(ApplicationConfig::YVES_STORAGE_SESSION_REDIS_HOST),
        ];
    }

}
