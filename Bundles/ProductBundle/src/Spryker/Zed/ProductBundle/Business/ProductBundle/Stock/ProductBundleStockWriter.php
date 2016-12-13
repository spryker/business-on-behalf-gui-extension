<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductBundle\Business\ProductBundle\Stock;

use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\StockProductTransfer;
use Spryker\Zed\ProductBundle\Business\ProductBundle\Availability\ProductBundleAvailabilityHandler;
use Spryker\Zed\ProductBundle\Communication\Plugin\Stock\ProductBundleAvailabilityHandlerPlugin;
use Spryker\Zed\ProductBundle\Dependency\QueryContainer\ProductBundleToStockQueryContainerInterface;
use Spryker\Zed\ProductBundle\Persistence\ProductBundleQueryContainerInterface;

class ProductBundleStockWriter
{

    /**
     * @var ProductBundleQueryContainerInterface
     */
    protected $productBundleQueryContainer;

    /**
     * @var \Spryker\Zed\ProductBundle\Dependency\QueryContainer\ProductBundleToStockQueryContainerInterface
     */
    protected $stockQueryContainer;

    /**
     * @var \Spryker\Zed\ProductBundle\Business\ProductBundle\Availability\ProductBundleAvailabilityHandler
     */
    protected $productBundleAvailabilityHandler;

    /**
     * @param \Spryker\Zed\ProductBundle\Persistence\ProductBundleQueryContainerInterface $productBundleQueryContainer
     * @param \Spryker\Zed\ProductBundle\Dependency\QueryContainer\ProductBundleToStockQueryContainerInterface $stockQueryContainer
     * @param \Spryker\Zed\ProductBundle\Business\ProductBundle\Availability\ProductBundleAvailabilityHandler $productBundleAvailabilityHandler
     */
    public function __construct(
        ProductBundleQueryContainerInterface $productBundleQueryContainer,
        ProductBundleToStockQueryContainerInterface $stockQueryContainer,
        ProductBundleAvailabilityHandler $productBundleAvailabilityHandler
    ) {
        $this->productBundleQueryContainer = $productBundleQueryContainer;
        $this->stockQueryContainer = $stockQueryContainer;
        $this->productBundleAvailabilityHandler = $productBundleAvailabilityHandler;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    public function updateStock(ProductConcreteTransfer $productConcreteTransfer)
    {
        $productConcreteTransfer->requireSku()
            ->requireIdProductConcrete();

        $bundleProductEntity = $this->productBundleQueryContainer
            ->queryBundleProductBySku($productConcreteTransfer->getSku())
            ->findOne();

        if ($bundleProductEntity === null) {
            return $productConcreteTransfer;
        }

        $bundleItems = $this->productBundleQueryContainer
            ->queryBundleProduct($productConcreteTransfer->getIdProductConcrete())
            ->find();

        $bundledItemStock = [];
        $bundledItemQuantity = [];
        foreach ($bundleItems as $bundleItemEntity) {
            $bundledProductEntity = $bundleItemEntity->getSpyProductRelatedByFkBundledProduct();

            $bundledItemQuantity[$bundledProductEntity->getIdProduct()] = $bundleItemEntity->getQuantity();

            $productStocks = $this->stockQueryContainer
                ->queryStockByProducts($bundledProductEntity->getIdProduct())
                ->find();

            foreach ($productStocks as $productStockEntity) {
                if (!isset($bundledItemStock[$productStockEntity->getFkStock()])) {
                    $bundledItemStock[$productStockEntity->getFkStock()] = [];
                }

                if (!isset($bundledItemStock[$productStockEntity->getFkStock()][$productStockEntity->getFkProduct()])) {
                    $bundledItemStock[$productStockEntity->getFkStock()][$productStockEntity->getFkProduct()] = [];
                }

                $bundledItemStock[$productStockEntity->getFkStock()][$productStockEntity->getFkProduct()] = $productStockEntity->getQuantity();
            }
        }

        $bundleTotalStockPerWarehause = [];
        foreach ($bundledItemStock as $idStock => $warehouseStock) {
            $bundleStock = 0;
            foreach ($warehouseStock as $idProduct => $productStockQuantity) {

                $quantity = $bundledItemQuantity[$idProduct];

                $itemStock = floor($productStockQuantity / $quantity);

                if ($bundleStock > $itemStock || $bundleStock == 0) {
                    $bundleStock = $itemStock;
                }
            }

            $bundleTotalStockPerWarehause[$idStock] = $bundleStock;
        }

        foreach ($bundleTotalStockPerWarehause as $idStock => $bundleStock) {

            $stockEntity = $this->stockQueryContainer
                ->queryStockByProducts($productConcreteTransfer->getIdProductConcrete())
                ->filterByFkStock($idStock)
                ->findOneOrCreate();

            $stockEntity->setQuantity($bundleStock);
            $stockEntity->save();

            $stockTransfer = new StockProductTransfer();
            $stockTransfer->setSku($productConcreteTransfer->getSku());
            $stockTransfer->setStockType($stockEntity->getStock()->getName());
            $stockTransfer->fromArray($stockEntity->toArray(), true);

            $productConcreteTransfer->addStock($stockTransfer);

        }

        $this->productBundleAvailabilityHandler->updateBundleAvailability($productConcreteTransfer->getSku());

        return $productConcreteTransfer;

    }
}
