<?php

namespace Akyos\CmsBundle\Command;

use Akyos\CmsBundle\Entity\Seo;
use Akyos\CmsBundle\Twig\CmsExtension;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CmsFixNamespacesCommand extends Command
{
	protected static $defaultName = 'cms:fix-namespaces';
	private EntityManagerInterface $em;
	private CmsExtension $cmsExtension;
	
	public function __construct(EntityManagerInterface $em, CmsExtension $cmsExtension)
	{
		$this->em = $em;
		$this->cmsExtension = $cmsExtension;
	}
	
	protected function configure()
	{
		$this->setDescription('Fix les namespaces du cms.');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);
		$seos = $this->em->getRepository(Seo::class)->findAll();
		
		foreach ($seos as $seo) {
			$initType = $seo->getType();
			
			if (!class_exists($initType)) {
				$type = $this->cmsExtension->getEntityNameSpace($initType);
				$seo->setType($type);
				$this->em->flush();
			}
		}
		
		$io->success('Changement terminé.');
		return 0;
	}
}
