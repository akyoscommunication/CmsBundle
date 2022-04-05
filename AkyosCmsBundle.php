<?php

namespace Akyos\CmsBundle;

use Akyos\CmsBundle\DependencyInjection\CmsBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AkyosCmsBundle extends Bundle
{
	public function getContainerExtension()
	{
		if (null === $this->extension) {
			$this->extension = new CmsBundleExtension();
		}
		return $this->extension;
	}
}