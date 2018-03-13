<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CompanyUser\Business\Model;

use Generated\Shared\Transfer\CompanyUserTransfer;

class CompanyUserPluginExecutor implements CompanyUserPluginExecutorInterface
{
    /**
     * @var array|\Spryker\Zed\CompanyUserExtension\Dependency\Plugin\CompanyUserPostSavePluginInterface[]
     */
    protected $companyUserPostSavePlugins;

    /**
     * @var array|\Spryker\Zed\CompanyUserExtension\Dependency\Plugin\CompanyUserHydrationPluginInterface[]
     */
    protected $companyUserHydrationPlugins;

    /**
     * @var array|\Spryker\Zed\CompanyUserExtension\Dependency\Plugin\CompanyUserPostCreatePluginInterface[]
     */
    protected $companyUserPostCreatePlugins;

    /**
     * @param \Spryker\Zed\CompanyUserExtension\Dependency\Plugin\CompanyUserPostSavePluginInterface[] $companyUserPostSavePlugins
     * @param \Spryker\Zed\CompanyUserExtension\Dependency\Plugin\CompanyUserPostCreatePluginInterface[] $companyUserPostCreatePlugins
     * @param \Spryker\Zed\CompanyUserExtension\Dependency\Plugin\CompanyUserHydrationPluginInterface[] $companyUserHydrationPlugins
     */
    public function __construct(
        array $companyUserPostSavePlugins = [],
        array $companyUserPostCreatePlugins = [],
        array $companyUserHydrationPlugins = []
    ) {
        $this->companyUserPostSavePlugins = $companyUserPostSavePlugins;
        $this->companyUserHydrationPlugins = $companyUserHydrationPlugins;
        $this->companyUserPostCreatePlugins = $companyUserPostCreatePlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\CompanyUserTransfer $companyUserTransfer
     *
     * @return \Generated\Shared\Transfer\CompanyUserTransfer
     */
    public function executePostSavePlugins(CompanyUserTransfer $companyUserTransfer): CompanyUserTransfer
    {
        foreach ($this->companyUserPostSavePlugins as $companyUserPostSavePlugin) {
            $companyUserTransfer = $companyUserPostSavePlugin->postSave($companyUserTransfer);
        }

        return $companyUserTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CompanyUserTransfer $companyUserTransfer
     *
     * @return \Generated\Shared\Transfer\CompanyUserTransfer
     */
    public function executeHydrationPlugins(CompanyUserTransfer $companyUserTransfer): CompanyUserTransfer
    {
        foreach ($this->companyUserHydrationPlugins as $companyUserHydrationPlugin) {
            $companyUserTransfer = $companyUserHydrationPlugin->hydrate($companyUserTransfer);
        }

        return $companyUserTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CompanyUserTransfer $companyUserTransfer
     *
     * @return \Generated\Shared\Transfer\CompanyUserTransfer
     */
    public function executePostCreatePlugins(CompanyUserTransfer $companyUserTransfer): CompanyUserTransfer
    {
        foreach ($this->companyUserPostCreatePlugins as $companyUserPostCreatePlugin) {
            $companyUserTransfer = $companyUserPostCreatePlugin->postCreate($companyUserTransfer);
        }

        return $companyUserTransfer;
    }
}
