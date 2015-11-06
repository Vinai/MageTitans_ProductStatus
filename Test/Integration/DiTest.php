<?php


namespace MageTitans\ProductStatus;

use Magento\Framework\Console\CommandList;
use Magento\TestFramework\Helper\Bootstrap;
use MageTitans\ProductStatus\Console\Command\DisableProductCommand;
use MageTitans\ProductStatus\Console\Command\DisableProductCommandTest;
use MageTitans\ProductStatus\Console\Command\EnableProductCommand;
use MageTitans\ProductStatus\Console\Command\ShowProductStatusCommand;

class DiTest extends \PHPUnit_Framework_TestCase
{
    public function testTheShowProductStatusCommandIsRegistered()
    {
        /** @var CommandList $commandList */
        $commandList = Bootstrap::getObjectManager()->create(CommandList::class);
        $commands = $commandList->getCommands();
        $this->assertArrayHasKey('catalogProductStatus', $commands);
        $this->assertInstanceOf(ShowProductStatusCommand::class, $commands['catalogProductStatus']);
    }

    public function testTheDisableProductCommandIsRegistered()
    {
        /** @var CommandList $commandList */
        $commandList = Bootstrap::getObjectManager()->create(CommandList::class);
        $commands = $commandList->getCommands();
        $this->assertArrayHasKey('catalogProductDisable', $commands);
        $this->assertInstanceOf(DisableProductCommand::class, $commands['catalogProductDisable']);
    }

    public function testTheEnableProductCommandIsRegistered()
    {
        /** @var CommandList $commandList */
        $commandList = Bootstrap::getObjectManager()->create(CommandList::class);
        $commands = $commandList->getCommands();
        $this->assertArrayHasKey('catalogProdctEnable', $commands);
        $this->assertInstanceOf(EnableProductCommand::class, $commands['catalogProductEnable']);
    }
}
