<?php

namespace Bara\Application\Service;

use Bara\Domain\Model\Admin;

interface AdminRepository
{

    public function nextIdentity(): string;

    public function add(Admin $admin): void;

    public function update(): void;

    public function isEmailAvailable(string $email): bool;

    public function ofId(string $adminId): Admin;
}
