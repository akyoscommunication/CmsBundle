<?php

declare(strict_types=1);

namespace Akyos\CmsBundle\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class SlugRedirect
{
    // Used in Akyos\CmsBundle\DoctrineListener\SlugRedirectListener
    // Add attribute on slug that are used in urls to prevent 404 on slug change
}
