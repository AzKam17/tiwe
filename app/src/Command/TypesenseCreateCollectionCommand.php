<?php

namespace App\Command;

use App\Service\ProductIndexerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'typesense:create',
    description: 'Create Typesense collections'
)]
class TypesenseCreateCollectionCommand extends Command
{
    public function __construct(
        private readonly ProductIndexerService $productIndexer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Creating Typesense Collections');

        try {
            $io->section('Creating products collection...');
            $this->productIndexer->createCollection();
            $io->success('Products collection created successfully');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to create collections: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
