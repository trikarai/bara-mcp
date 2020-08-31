<?php

namespace Query\Application\Service\Firm\Client\ProgramParticipation;

use Query\ {
    Application\Service\Firm\Program\ConsulationSetup\ConsultationSessionFilter,
    Domain\Model\Firm\Program\ConsultationSetup\ConsultationSession
};

interface ConsultationSessionRepository
{

    public function aConsultationSessionOfClient(
            string $firmId, string $clientId, string $programId, string $consultationSessionId): ConsultationSession;

    public function allConsultationsSessionOfClient(
            string $firmId, string $clientId, string $programId, int $page, int $pageSize, ConsultationSessionFilter $consultationSessionFilter);
}
