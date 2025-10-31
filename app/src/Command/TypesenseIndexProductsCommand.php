<?php

namespace App\Command;

use App\Service\ProductIndexerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'typesense:import',
    description: 'Import all products to Typesense'
)]
class TypesenseIndexProductsCommand extends Command
{
    public function __construct(
        private readonly ProductIndexerService $productIndexer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Indexing Products to Typesense');

        try {
            $io->section('Indexing products...');
            $count = $this->productIndexer->indexAllProducts();
            $io->success("Successfully indexed {$count} products");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to index products: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
