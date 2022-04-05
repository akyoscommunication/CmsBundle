<?php

namespace Akyos\CmsBundle\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class SlugRedirect
{
	// Used in Akyos\CmsBundle\DoctrineListener\SlugRedirectListener
	// Add annotation on slug that are used in urls to prevent 404 on slug change
}