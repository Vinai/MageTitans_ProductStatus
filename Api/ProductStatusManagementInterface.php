<?php


namespace MageTitans\ProductStatus\Api;

interface ProductStatusManagementInterface
{
    /**
     * @param string $sku
     * @return string
     */
    public function get($sku);
}
