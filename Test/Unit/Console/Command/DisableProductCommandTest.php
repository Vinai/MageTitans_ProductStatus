<?php


namespace MageTitans\ProductStatus\Console\Command;

use MageTitans\ProductStatus\Model\Exception\ProductStatusAdapterException;
use MageTitans\ProductStatus\Model\ProductStatusAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableProductCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DisableProductCommand
     */
    private $command;

    /**
     * @var InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockInput;

    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockOutput;

    /**
     * @var ProductStatusAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productStatusAdapter;

    protected function setUp()
    {
        $this->productStatusAdapter = $this->getMock(ProductStatusAdapterInterface::class);
        $this->command = new DisableProductCommand($this->productStatusAdapter);
        $this->mockInput = $this->getMock(InputInterface::class);
        $this->mockOutput = $this->getMock(OutputInterface::class);
    }

    public function testItIsAConsoleCommand()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testItHasAName()
    {
        $this->assertSame('catalog:product:disable', $this->command->getName());
    }

    public function testItHasADescription()
    {
        $this->assertNotEmpty($this->command->getDescription());
    }

    public function testItTakesARequiredSkuArgument()
    {
        $argument = $this->command->getDefinition()->getArgument('sku');
        $this->assertTrue($argument->isRequired());
        $this->assertNotEmpty($argument->getDescription());
    }

    public function testItDisplaysExceptionsAsErrors()
    {
        $testMessage = 'Test Exception';
        $this->productStatusAdapter->method('disableProductWithSku')
            ->with('test')
            ->willThrowException(new ProductStatusAdapterException($testMessage));
        $this->mockInput->method('getArgument')->willReturn('test');
        $this->mockOutput->expects($this->once())->method('writeln')->with('<error>' . $testMessage . '</error>');
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysAConfirmationMessage()
    {
        $this->mockInput->method('getArgument')->willReturn('test');
        $this->mockOutput->expects($this->once())->method('writeln')->with('<info>Disabled product "test"</info>');
        $this->command->run($this->mockInput, $this->mockOutput);
    }
}
