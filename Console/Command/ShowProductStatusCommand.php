<?php


namespace MageTitans\ProductStatus\Console\Command;

use MageTitans\ProductStatus\Model\Exception\ProductStatusAdapterException;
use MageTitans\ProductStatus\Model\ProductStatusAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowProductStatusCommand extends Command
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
        $this->setName('catalog:product:status');
        $this->setDescription('Display the status for products with matching SKU');
        $this->addArgument('sku', InputArgument::REQUIRED, 'Display the status for products matching the SKU');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $skuPattern = $input->getArgument('sku');
            $matches = $this->productStatusAdapter->getProductStatusMatchingSku($skuPattern);
            if ($matches) {
                $this->outputMatches($output, $matches);
            } else {
                $output->writeln(sprintf('<comment>No matches found for "%s"</comment>', $skuPattern));
            }
        } catch (ProductStatusAdapterException $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
        }
    }

    /**
     * @param OutputInterface $output
     * @param string[] $matches
     */
    private function outputMatches(OutputInterface $output, array $matches)
    {
        array_map(function ($sku, $status) use ($matches, $output) {
            $output->writeln(sprintf('<info>%s: %s</info>', $sku, $status));
        }, array_keys($matches), $matches);
    }
}
