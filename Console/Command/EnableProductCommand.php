<?php


namespace MageTitans\ProductStatus\Console\Command;

use MageTitans\ProductStatus\Model\Exception\ProductStatusAdapterException;
use MageTitans\ProductStatus\Model\ProductStatusAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnableProductCommand extends Command
{
    /**
     * @var ProductStatusAdapterInterface
     */
    private $productStatusAdapter;

    public function __construct(ProductStatusAdapterInterface $productStatusAdapter)
    {
        parent::__construct();
        $this->productStatusAdapter = $productStatusAdapter;
    }

    protected function configure()
    {
        $this->setName('catalog:product:enable');
        $this->setDescription('Enable product with given SKU');
        $this->addArgument('sku', InputArgument::REQUIRED, 'The SKU to enable');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $sku = $input->getArgument('sku');
            $this->productStatusAdapter->enableProductWithSku($sku);
            $output->writeln(sprintf('<info>Enabled product "%s"</info>', $sku));
        } catch (ProductStatusAdapterException $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
        }
    }
}
