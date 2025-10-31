<?php

namespace App\Command;

use App\Service\TypesenseClientService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'typesense:drop',
    description: 'Drop all Typesense collections'
)]
class TypesenseDropCollectionsCommand extends Command
{
    public function __construct(
        private readonly TypesenseClientService $typesenseClient
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force deletion without confirmation'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');

        $io->title('Drop Typesense Collections');

        try {
            // Get all collections
            $collections = $this->typesenseClient->getCollections()->retrieve();

            if (empty($collections)) {
                $io->info('No collections found in Typesense');
                return Command::SUCCESS;
            }

            $collectionNames = array_map(fn($col) => $col['name'], $collections);

            $io->section('Found collections:');
            $io->listing($collectionNames);

            // Confirm deletion
            if (!$force) {
                if (!$io->confirm(
                    sprintf('Are you sure you want to delete %d collection(s)?', count($collectionNames)),
                    false
                )) {
                    $io->warning('Operation cancelled');
                    return Command::SUCCESS;
                }
            }

            // Delete each collection
            $io->section('Deleting collections...');
            $io->progressStart(count($collectionNames));

            $deleted = 0;
            $errors = [];

            foreach ($collectionNames as $collectionName) {
                try {
                    $this->typesenseClient->deleteCollection($collectionName);
                    $deleted++;
                    $io->progressAdvance();
                } catch (\Exception $e) {
                    $errors[] = sprintf(
                        'Failed to delete collection "%s": %s',
                        $collectionName,
                        $e->getMessage()
                    );
                    $io->progressAdvance();
                }
            }

            $io->progressFinish();

            // Display results
            if ($deleted > 0) {
                $io->success(sprintf('Successfully deleted %d collection(s)', $deleted));
            }

            if (!empty($errors)) {
                $io->error('Some collections could not be deleted:');
                $io->listing($errors);
                return Command::FAILURE;
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to retrieve collections: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
