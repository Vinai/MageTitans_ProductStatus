<?php


namespace MageTitans\ProductStatus\Console\Command;

use MageTitans\ProductStatus\Model\Exception\ProductStatusAdapterException;
use MageTitans\ProductStatus\Model\ProductStatusAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowProductStatusCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShowProductStatusCommand
     */
    private $command;

    /**
     * @var ProductStatusAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductStatusAdapter;

    /**
     * @var InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockInput;

    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockOutput;

    protected function setUp()
    {
        $this->mockProductStatusAdapter = $this->getMock(ProductStatusAdapterInterface::class);
        $this->command = new ShowProductStatusCommand($this->mockProductStatusAdapter);
        $this->mockInput = $this->getMock(InputInterface::class);
        $this->mockOutput = $this->getMock(OutputInterface::class);
    }

    public function testItIsAConsoleCommand()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testItHasAName()
    {
        $this->assertSame('catalog:product:status', $this->command->getName());
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

    public function testItDisplaysExceptionsAsErrorMessages()
    {
        $message = 'Test Message';
        $this->mockProductStatusAdapter->method('getProductStatusMatchingSku')
            ->willThrowException(new ProductStatusAdapterException($message));
        $this->mockOutput->expects($this->once())->method('writeln')->with('<error>' . $message . '</error>');

        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysAMessageIfThereAreNoMatches()
    {
        $this->mockInput->method('getArgument')->with('sku')->willReturn('TEST');
        $this->mockProductStatusAdapter->method('getProductStatusMatchingSku')->willReturn([]);
        $this->mockOutput->expects($this->once())->method('writeln')
            ->with('<comment>No matches found for "TEST"</comment>');

        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysTheStatusForAllMatchingProducts()
    {
        $this->mockInput->method('getArgument')->with('sku')->willReturn('TEST');
        $this->mockProductStatusAdapter->method('getProductStatusMatchingSku')->willReturn([
            'TEST1' => ProductStatusAdapterInterface::ENABLED,
            'TEST2' => ProductStatusAdapterInterface::DISABLED,
            'TEST3' => ProductStatusAdapterInterface::ENABLED
        ]);
        $this->mockOutput->method('writeln')->withConsecutive(
            ['<info>TEST1: enabled</info>'],
            ['<info>TEST2: disabled</info>'],
            ['<info>TEST3: enabled</info>']
        );
        $this->command->run($this->mockInput, $this->mockOutput);
    }
}
