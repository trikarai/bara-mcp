<?php

namespace Query\Application\Service\Firm\Client\ProgramParticipation;

use Query\{
    Application\Service\Firm\Program\ConsulationSetup\ConsultationRequestFilter,
    Domain\Model\Firm\Program\ConsultationSetup\ConsultationRequest
};

interface ConsultationRequestRepository
{

    public function aConsultationRequestOfClient(
            string $firmId, string $clientId, string $programId, string $consultationRequestId): ConsultationRequest;

    public function allConsultationRequestsOfClient(
            string $firmId, string $clientId, string $programId, int $page, int $pageSize,
            ?ConsultationRequestFilter $consultationRequestFilter);
}
