<?php

namespace Umbrella\AdminBundle\Service;

use Umbrella\AdminBundle\Entity\BaseAdminUser;
use Umbrella\AdminBundle\Exception\ResetPasswordException;

interface UserManagerInterface
{
    public function create(): BaseAdminUser;

    public function find(int $id): ?BaseAdminUser;

    public function updatePassword(BaseAdminUser $user): void;

    public function delete(BaseAdminUser $user): void;

    public function save(BaseAdminUser $user): void;

    /**
     * @throws ResetPasswordException
     */
    public function sendResetPasswordEmail(string $email): void;

    /**
     * @throws ResetPasswordException
     */
    public function validateResetPasswordTokenAndFetchUser(string $token): BaseAdminUser;
}
