<?php


namespace MageTitans\ProductStatus\Api;

interface ProductStatusManagementInterface
{
    /**
     * @param string $sku
     * @return string
     */
    public function get($sku);

    /**
     * @param string $sku
     * @param string $status
     * @return string
     */
    public function set($sku, $status);
}
