<?php

namespace Firm\Application\Service\Personnel;

use Firm\Domain\Model\Firm\Program\ActivityType;

interface ActivityTypeRepository
{
    public function nextIdentity(): string;
    
    public function add(ActivityType $activityType): void;

    public function ofId(string $activityTypeId): ActivityType;
    
    public function update(): void;
}
