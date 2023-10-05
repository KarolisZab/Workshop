<?php

namespace App\Command;

use App\Document\Workshop;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateWorkshopCommand extends Command
{
    protected static $defaultName = 'app:create:workshop';

    public function __construct(private DocumentManager $documentManager) 
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('blah blah');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workshop = new Workshop();
        $workshop->setTitle('testtest')
            ->setCategory('Mekanik');

        $this->documentManager->persist($workshop);
        $this->documentManager->flush();

        return 0;
    }
}
