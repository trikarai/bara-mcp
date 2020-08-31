<?php

namespace Firm\Application\Listener\Firm\Program\ConsultationSetup;

use Resources\Application\Event\Event;

interface UserUpdatedConsultationRequestEventInterface extends Event
{

    public function getUserId(): string;

    public function getFirmId(): string;

    public function getProgramId(): string;

    public function getConsultationRequestId(): string;
}
