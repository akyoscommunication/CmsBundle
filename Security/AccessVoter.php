<?php

namespace Akyos\CmsBundle\Security;

use Akyos\CmsBundle\Repository\AdminAccessRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccessVoter extends Voter
{
    private readonly Security $security;

    public function __construct(Security $security, private readonly AdminAccessRepository $adminAccessRepository)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->adminAccessRepository->findOneBy(['slug' => $attribute]) && $this->security->getUser();
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $role = $this->adminAccessRepository->findOneBy(['slug' => $attribute]);
        $r = true;

        if ($role) {
            $authorizedRoles = $role->getRoles();
            if (!empty($authorizedRoles)) {
                $r = false;
                foreach ($authorizedRoles as $authorizedRole) {
                    if ($this->security->isGranted($authorizedRole)) {
                        $r = true;
                        break;
                    }
                }
            }
        }

        return $r;
    }
}
