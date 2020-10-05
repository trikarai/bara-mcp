<?php

namespace Notification\Application\Listener;

use Notification\Domain\Model\User;

interface UserRepository
{
    public function ofId(string $userId): User;
}
