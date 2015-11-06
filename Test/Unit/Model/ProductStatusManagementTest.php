<?php


namespace MageTitans\ProductStatus\Model;

use MageTitans\ProductStatus\Api\ProductStatusManagementInterface;
use MageTitans\ProductStatus\Model\Exception\ProductAlreadyDisabledException;
use MageTitans\ProductStatus\Model\Exception\ProductAlreadyEnabledException;

class ProductStatusManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductStatusManagement
     */
    private $api;

    /**
     * @var ProductStatusAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductStatusAdapter;

    protected function setUp()
    {
        $this->mockProductStatusAdapter = $this->getMock(ProductStatusAdapterInterface::class);
        $this->api = new ProductStatusManagement($this->mockProductStatusAdapter);
    }
    
    public function testItImplementsTheProductStatusManagementInterface()
    {
        $this->assertInstanceOf(ProductStatusManagementInterface::class, $this->api);
    }

    public function testItReturnsTheProductStatus()
    {
        $this->mockProductStatusAdapter->method('getStatusBySku')->willReturnMap([
            ['test1', ProductStatusAdapterInterface::ENABLED],
            ['test2', ProductStatusAdapterInterface::DISABLED],
        ]);
        $this->assertSame(ProductStatusAdapterInterface::ENABLED, $this->api->get('test1'));
        $this->assertSame(ProductStatusAdapterInterface::DISABLED, $this->api->get('test2'));
    }

    public function testItEnablesAProduct()
    {
        $this->mockProductStatusAdapter->expects($this->once())->method('enableProductWithSku')->with('test');
        $this->api->set('test', 'enabled');
    }

    public function testItDisablesAProduct()
    {
        $this->mockProductStatusAdapter->expects($this->once())->method('disableProductWithSku')->with('test');
        $this->api->set('test', 'disabled');
    }

    public function testItThrowsAnExceptionIfTheStatusIsInvalid()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The product status to set has to be "enabled" or "disabled"'
        );
        $this->api->set('test', 'entangled');
    }

    public function testItHidesProductAlreadyDisabledExceptions()
    {
        $this->mockProductStatusAdapter->method('disableProductWithSku')
            ->willThrowException(new ProductAlreadyDisabledException('Dummy Exception'));
        $this->assertNotNull($this->api->set('test', 'disabled'));
    }

    public function testItHidesProductAlreadyEnabledExceptions()
    {
        $this->mockProductStatusAdapter->method('enableProductWithSku')
            ->willThrowException(new ProductAlreadyEnabledException('Dummy Exception'));
        $this->assertNotNull($this->api->set('test', 'enabled'));
    }

    public function testItReturnsTheNewProductStatus()
    {
        $this->assertSame('enabled', $this->api->set('test', 'enabled'));
        $this->assertSame('disabled', $this->api->set('test', 'disabled'));
    }
}
