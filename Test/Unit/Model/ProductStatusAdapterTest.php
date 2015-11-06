<?php


namespace MageTitans\ProductStatus\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\NoSuchEntityException;
use MageTitans\ProductStatus\Model\Exception\InvalidSkuException;
use MageTitans\ProductStatus\Model\Exception\ProductAlreadyDisabledException;
use MageTitans\ProductStatus\Model\Exception\ProductStatusAdapterException;

class ProductStatusAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductStatusAdapter
     */
    private $productStatusAdapter;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductRepository;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSearchCriteriaBuilder;

    /**
     * @var ProductSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSearchResult;

    /**
     * @var AppState|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockAppState;

    /**
     * @param string $sku
     * @return ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProduct($sku)
    {
        $mockProduct = $this->getMock(ProductInterface::class);
        $mockProduct->method('getSku')->willReturn($sku);
        return $mockProduct;
    }

    /**
     * @param string $sku
     * @param int $status
     * @return ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductWithStatus($sku, $status)
    {
        $mockProduct = $this->createMockProduct($sku);
        $mockProduct->method('getStatus')->willReturn($status);
        return $mockProduct;
    }

    /**
     * @param string $sku
     * @return ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockEnabledProduct($sku)
    {
        return $this->createMockProductWithStatus($sku, ProductStatus::STATUS_ENABLED);
    }

    /**
     * @param string $sku
     * @return ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockDisabledProduct($sku)
    {
        return $this->createMockProductWithStatus($sku, ProductStatus::STATUS_DISABLED);
    }

    protected function setUp()
    {
        $this->mockSearchResult = $this->getMock(ProductSearchResultsInterface::class);
        $this->mockProductRepository = $this->getMock(ProductRepositoryInterface::class);
        $this->mockProductRepository->method('getList')->willReturn($this->mockSearchResult);
        $this->mockSearchCriteriaBuilder = $this->getMock(SearchCriteriaBuilder::class, [], [], '', false);
        $this->mockSearchCriteriaBuilder->method('create')
            ->willReturn($this->getMock(SearchCriteria::class, [], [], '', false));
        $this->mockAppState = $this->getMock(AppState::class, [], [], '', false);
        $this->productStatusAdapter = new ProductStatusAdapter(
            $this->mockProductRepository,
            $this->mockSearchCriteriaBuilder,
            $this->mockAppState
        );
    }

    public function testItImplementsTheProductStatusAdapterInterface()
    {
        $this->assertInstanceOf(ProductStatusAdapterInterface::class, $this->productStatusAdapter);
    }

    public function testItThrowsAnExceptionIfTheSkuToListIsNoString()
    {
        $this->setExpectedException(InvalidSkuException::class, 'The specified SKU has to be a string');
        $this->productStatusAdapter->getProductStatusMatchingSku(123);
    }

    /**
     * @param string $emptySku
     * @dataProvider emptySkuStringDataProvider
     */
    public function testItThrowsAnExceptionIfTheSkuToListIsEmpty($emptySku)
    {
        $this->setExpectedException(InvalidSkuException::class, 'The specified SKU must not be empty');
        $this->productStatusAdapter->getProductStatusMatchingSku($emptySku);
    }

    public function emptySkuStringDataProvider()
    {
        return [[''], [' ']];
    }

    public function testItQueriesAProductRepository()
    {
        $this->mockSearchResult->expects($this->once())->method('getItems')->willReturn([]);
        $this->productStatusAdapter->getProductStatusMatchingSku('test');
    }

    public function testItReturnsAnEmptyArrayIfThereIsNoMatch()
    {
        $this->mockSearchResult->expects($this->once())->method('getItems')
            ->willReturn([]);
        $this->assertSame([], $this->productStatusAdapter->getProductStatusMatchingSku('test'));
    }

    public function testItTranslatesTheProductRepositorySearchResultsIntoStatusArray()
    {
        $this->mockSearchResult->method('getItems')->willReturn([
            $this->createMockEnabledProduct('test1'),
            $this->createMockDisabledProduct('test2'),
        ]);
        $expected = [
            'test1' => 'enabled',
            'test2' => 'disabled'
        ];
        $this->assertSame($expected, $this->productStatusAdapter->getProductStatusMatchingSku('test'));
    }

    public function testItAddsTheSkuAsSerchCriteria()
    {
        $this->mockSearchCriteriaBuilder->expects($this->once())->method('addFilter')->with('sku', '%test%', 'like');
        $this->mockSearchResult->expects($this->once())->method('getItems')->willReturn([]);
        $this->productStatusAdapter->getProductStatusMatchingSku('test');
    }

    public function testItThrowsAnExceptionIfTheSkuToDisableIsNotAString()
    {
        $this->setExpectedException(InvalidSkuException::class, 'The specified SKU has to be a string');
        $this->productStatusAdapter->disableProductWithSku([]);
    }

    public function testItThrowsAnExceptionIfTheSkuToDisableIsEmpty()
    {
        $this->setExpectedException(InvalidSkuException::class, 'The specified SKU must not be empty');
        $this->productStatusAdapter->disableProductWithSku(' ');
    }

    public function testItThrowsAnExceptionIfTheSpecifiedSkuAlreadyIsDisabled()
    {
        $this->setExpectedException(ProductAlreadyDisabledException::class, 'The product "test" already is disabled');
        $this->mockProductRepository->method('get')->willReturn($this->createMockDisabledProduct('test'));
        $this->productStatusAdapter->disableProductWithSku('test');
    }

    public function testItDisablesAnEnabledProduct()
    {
        $mockProduct = $this->createMockEnabledProduct('test');
        $mockProduct->expects($this->once())->method('setStatus')->with(ProductStatus::STATUS_DISABLED);
        $this->mockProductRepository->method('get')->willReturn($mockProduct);
        $this->mockProductRepository->expects($this->once())->method('save')->with($mockProduct);
        $this->productStatusAdapter->disableProductWithSku('test');
    }

    public function testItConvertsEntityNotFoundExceptionsToProductStatusCommandExceptionsForDisable()
    {
        $testException = new NoSuchEntityException();
        $this->mockProductRepository->method('get')
            ->willThrowException($testException);
        $this->setExpectedException(ProductStatusAdapterException::class);

        $this->productStatusAdapter->disableProductWithSku('test');
    }

    public function testItSetsTheAppState()
    {
        $this->mockAppState->expects($this->once())->method('setAreaCode');
        new ProductStatusAdapter(
            $this->mockProductRepository,
            $this->mockSearchCriteriaBuilder,
            $this->mockAppState
        );
    }
}
