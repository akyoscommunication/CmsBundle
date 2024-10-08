<?php

namespace Akyos\CmsBundle\Security;

use Akyos\CmsBundle\Repository\AdminAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

class AccessVoter extends Voter
{
    private Security $security;

    private AdminAccessRepository $adminAccessRepository;

    public function __construct(Security $security, AdminAccessRepository $adminAccessRepository)
    {
        $this->security = $security;
        $this->adminAccessRepository = $adminAccessRepository;
    }

    protected function supports($attribute, $subject): bool
    {
        return $this->adminAccessRepository->findOneBy(['slug' => $attribute]) && $this->security->getUser();
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
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
