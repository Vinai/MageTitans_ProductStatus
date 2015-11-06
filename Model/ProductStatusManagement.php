<?php


namespace MageTitans\ProductStatus\Model;

use MageTitans\ProductStatus\Api\ProductStatusManagementInterface;

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
     * @param string $sku
     * @return string
     */
    public function get($sku)
    {
        return $this->productStatusAdapter->getStatusForProductWithSku($sku);
    }
}
