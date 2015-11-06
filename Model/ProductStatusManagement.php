<?php


namespace MageTitans\ProductStatus\Model;

use MageTitans\ProductStatus\Api\ProductStatusManagementInterface;
use MageTitans\ProductStatus\Model\Exception\ProductAlreadyDisabledException;
use MageTitans\ProductStatus\Model\Exception\ProductAlreadyEnabledException;

class ProductStatusManagement implements ProductStatusManagementInterface
{
    /**
     * @var ProductStatusAdapterInterface
     */
    private $productStatusAdapter;

    public function __construct(ProductStatusAdapterInterface $productStatusAdapter)
    {
        $this->productStatusAdapter = $productStatusAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sku)
    {
        return $this->productStatusAdapter->getStatusForProductWithSku($sku);
    }

    /**
     * [@inheritdoc]
     */
    public function set($sku, $status)
    {
        switch ($status) {
            case ProductStatusAdapterInterface::ENABLED:
                $this->enableProductWithSku($sku);
                break;
            case ProductStatusAdapterInterface::DISABLED:
                $this->disableProductWithSku($sku);
                break;
            default:
                $this->throwInvalidProductStatusException();
        }
        return $status;
    }

    /**
     * @param string $sku
     */
    private function enableProductWithSku($sku)
    {
        try {
            $this->productStatusAdapter->enableProductWithSku($sku);
        } catch (ProductAlreadyEnabledException $exception) {
        }
    }

    /**
     * @param string $sku
     */
    private function disableProductWithSku($sku)
    {
        try {
            $this->productStatusAdapter->disableProductWithSku($sku);
        } catch (ProductAlreadyDisabledException $exception) {
        };
    }

    private function throwInvalidProductStatusException()
    {
        throw new \InvalidArgumentException(sprintf(
            'The product status to set has to be "%s" or "%s"',
            ProductStatusAdapterInterface::ENABLED,
            ProductStatusAdapterInterface::DISABLED
        ));
    }
}
