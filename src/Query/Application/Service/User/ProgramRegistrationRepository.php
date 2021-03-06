<?php

namespace Query\Application\Service\User;

use Query\Domain\Model\User\UserRegistrant;

interface ProgramRegistrationRepository
{

    public function aProgramRegistrationOfUser(string $userId, string $programRegistrationId): UserRegistrant;

    public function allProgramRegistrationsOfUser(string $userId, int $page, int $pageSize);
}
