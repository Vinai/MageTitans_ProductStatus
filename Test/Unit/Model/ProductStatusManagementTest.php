<?php


namespace MageTitans\ProductStatus\Model;

use MageTitans\ProductStatus\Api\ProductStatusManagementInterface;

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
        $this->mockProductStatusAdapter->method('getStatusForProductWithSku')->willReturnMap([
            ['test1', ProductStatusAdapterInterface::ENABLED],
            ['test2', ProductStatusAdapterInterface::DISABLED],
        ]);
        $this->assertSame(ProductStatusAdapterInterface::ENABLED, $this->api->get('test1'));
        $this->assertSame(ProductStatusAdapterInterface::DISABLED, $this->api->get('test2'));
    }
}
