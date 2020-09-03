<?php

namespace Query\Application\Service\Firm\Program\ConsulationSetup;

use Query\ {
    Application\Service\Firm\Client\ProgramParticipation\ConsultationSessionRepository as InterfaceForClient,
    Application\Service\Firm\Personnel\ProgramConsultant\ConsultationSessionRepository as InterfaceForPersonnel,
    Application\Service\User\ProgramParticipation\ConsultationSessionRepository as InterfaceForUser,
    Domain\Model\Firm\Program\ConsultationSetup\ConsultationSession
};

interface ConsultationSessionRepository extends InterfaceForPersonnel, InterfaceForClient, InterfaceForUser
{

    public function ofId(string $firmId, string $programId, string $consultationSetupId, string $consultationSessionId): ConsultationSession;

    public function all(
            string $firmId, string $programId, string $consultationSetupId,
            ?ConsultationSessionFilter $consultationSessionFilter): ConsultationSession;
}
