<?php

/*
 * This file is part of the Sonata package.
*
* (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sonata\MediaBundle\Command;
use Symfony\Component\Console\Input\InputArgument;

use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Document\MediaManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * This command can be used to re-generate the thumbnails for all uploaded medias.
 * 
 * Useful if you have existing media content and added new formats.
 *
 */
class SyncThumbsCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('sonata:media:sync-thumbnails')
            ->setDescription('Sync uploaded image thumbs with new media formats')
            ->setDefinition(array(
                new InputArgument('context', InputArgument::REQUIRED, 'The context'),
                new InputArgument('providerName', InputArgument::REQUIRED, 'The provider'),
        ));
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $context = $input->getArgument('context');
        $provider = $input->getArgument('providerName');
        
        $container = $this->getContainer();
        $manager = $container->get('sonata.media.manager.media');
        $medias = $manager->findBy(array('providerName' => $provider));
        
        $output->writeln(sprintf("Loaded %s images for generating thumbs (provider: %s)", count($medias), $provider));
        
        foreach ($medias as $media) {
            $provider = $manager->getPool()->getProvider($media->getProviderName());
            $output->writeln("Generating thumbs for " . $media->getName());
            $provider->removeThumbnails($media);
            $provider->generateThumbnails($media);
        }
    }
}
