<?php

namespace Akyos\CmsBundle\DoctrineListener;

use Akyos\CmsBundle\Annotations\SlugRedirect;
use Akyos\CmsBundle\Entity\Redirect301;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use ReflectionObject;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', lazy: true)]
class SlugRedirectListener
{
    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $reflectionObject = new ReflectionObject($entity);

        if (!$reflectionObject->hasProperty('slug')) {
            return;
        }

        $reflectionProperty = $reflectionObject->getProperty('slug');
        if ([] === $reflectionProperty->getAttributes(SlugRedirect::class)) {
            return;
        }

        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($entity);

        if (!array_key_exists('slug', $changeSet)) {
            return;
        }

        /** @var Redirect301|null $sameNewSlugRedirect */
        $sameNewSlugRedirect = $em->getRepository(Redirect301::class)->findOneBy(['newSlug' => $changeSet['slug'][0], 'objectType' => $reflectionObject->getName()]);
        /** @var Redirect301|null $sameOldSlugRedirect */
        $sameOldSlugRedirect = $em->getRepository(Redirect301::class)->findOneBy(['oldSlug' => $changeSet['slug'][1], 'objectType' => $reflectionObject->getName()]);

        if (!$sameNewSlugRedirect && !$sameOldSlugRedirect) {
            $redirect = new Redirect301();
            $redirect->setObjectId($entity->getId());
            $redirect->setObjectType($reflectionObject->getName());
            $redirect->setOldSlug($changeSet['slug'][0]);
            $redirect->setNewSlug('(' . $changeSet['slug'][1] . ')');
            $em->persist($redirect);
            $em->flush();
        }

        if ($sameNewSlugRedirect && $sameNewSlugRedirect->getObjectId() === $entity->getId()) {
            $sameNewSlugRedirect->setNewSlug($changeSet['slug'][1]);
            if (!$sameOldSlugRedirect) {
                $sameNewSlugRedirect->setNewSlug('(' . $changeSet['slug'][1] . ')');
            } elseif ($sameOldSlugRedirect->getObjectId() === $entity->getId()) {
                $sameNewSlugRedirect->setNewSlug('(' . $changeSet['slug'][1] . ')');
            }
            $em->flush();
        }

        if ($sameOldSlugRedirect && $sameOldSlugRedirect->getObjectId() !== $entity->getId()) {
            if (!$sameNewSlugRedirect) {
                $redirect = new Redirect301();
                $redirect->setObjectId($entity->getId());
                $redirect->setObjectType($reflectionObject->getName());
                $redirect->setOldSlug($changeSet['slug'][0]);
                $redirect->setNewSlug($changeSet['slug'][1]);
                $em->persist($redirect);
                $em->flush();
            }

            $entity->setSlug($changeSet['slug'][1] . '-1');
            $em->flush();
        }
    }
}
