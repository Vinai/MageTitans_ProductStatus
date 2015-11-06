<?php


namespace MageTitans\ProductStatus\Model;

interface ProductStatusAdapterInterface
{
    const ENABLED = 'enabled';
    const DISABLED = 'disabled';
    
    /**
     * @param string $sku
     * @return string[]
     */
    public function getProductStatusMatchingSku($sku);

    /**
     * @param string $sku
     * @return void
     */
    public function disableProductWithSku($sku);
}
