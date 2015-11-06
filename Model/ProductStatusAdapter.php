<?php


namespace MageTitans\ProductStatus\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\NoSuchEntityException;
use MageTitans\ProductStatus\Model\Exception\InvalidSkuException;
use MageTitans\ProductStatus\Model\Exception\ProductAlreadyDisabledException;
use MageTitans\ProductStatus\Model\Exception\ProductAlreadyEnabledException;
use MageTitans\ProductStatus\Model\Exception\ProductStatusAdapterException;

class ProductStatusAdapter implements ProductStatusAdapterInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    
    private $statusMap = [
        ProductStatus::STATUS_ENABLED => ProductStatusAdapterInterface::ENABLED,
        ProductStatus::STATUS_DISABLED => ProductStatusAdapterInterface::DISABLED
    ];

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AppState $appState
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        try {
            $appState->setAreaCode('adminhtml');
        } catch (\Exception $exception) {};
    }

    /**
     * @param string $sku
     * @return string[]
     */
    public function getProductStatusMatchingSku($sku)
    {
        $this->validateSku($sku);
        $this->searchCriteriaBuilder->addFilter('sku', '%' . $sku . '%', 'like');
        $result = $this->productRepository->getList($this->searchCriteriaBuilder->create());
        return array_reduce($result->getItems(), function ($acc, ProductInterface $product) {
            return array_merge($acc, [$product->getSku() => $this->getStatusAsString($product)]);
        }, []);
    }

    /**
     * @param string $sku
     */
    private function validateSku($sku)
    {
        if (!is_string($sku)) {
            throw new InvalidSkuException('The specified SKU has to be a string');
        }
        if (empty(trim($sku))) {
            throw new InvalidSkuException('The specified SKU must not be empty');
        }
    }

    /**
     * @param ProductInterface $product
     * @return string
     */
    private function getStatusAsString(ProductInterface $product)
    {
        return $this->statusMap[$product->getStatus()];
    }

    /**
     * @param string $sku
     */
    public function disableProductWithSku($sku)
    {
        $this->validateSku($sku);
        try {
            $product = $this->productRepository->get($sku);
            if ($product->getStatus() === ProductStatus::STATUS_DISABLED) {
                throw new ProductAlreadyDisabledException(sprintf('The product "%s" already is disabled', $sku));
            }
            $product->setStatus(ProductStatus::STATUS_DISABLED);
            $this->productRepository->save($product);
        } catch (NoSuchEntityException $exception) {
            throw new ProductStatusAdapterException($exception->getMessage());
        }
    }

    /**
     * @param string $sku
     */
    public function enableProductWithSku($sku)
    {
        $this->validateSku($sku);
        try {
            $product = $this->productRepository->get($sku);
            if ($product->getStatus() == ProductStatus::STATUS_ENABLED) {
                throw new ProductAlreadyEnabledException(sprintf('The product "%s" already is enabled', $sku));
            }
            $product->setStatus(ProductStatus::STATUS_ENABLED);
            $this->productRepository->save($product);
        } catch (NoSuchEntityException $exception) {
            throw new ProductStatusAdapterException($exception->getMessage());
        }
    }

    /**
     * @param string $sku
     * @return string
     */
    public function getStatusForProductWithSku($sku)
    {
        $this->validateSku($sku);
        $product = $this->productRepository->get($sku);
        return $this->getStatusAsString($product);
    }
}
