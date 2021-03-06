<?php

namespace Firm\Application\Service\Firm\Program;

use Firm\Domain\Model\Firm\Program\RegistrationPhaseData;

class RegistrationPhaseUpdate
{

    /**
     *
     * @var RegistrationPhaseRepository
     */
    protected $registrationPhaseRepository;

    function __construct(RegistrationPhaseRepository $registrationPhaseRepository)
    {
        $this->registrationPhaseRepository = $registrationPhaseRepository;
    }

    public function execute(
            ProgramCompositionId $programCompositionId, string $registrationPhaseId,
            RegistrationPhaseData $registrationPhaseData): void
    {
        $registrationPhase = $this->registrationPhaseRepository->ofId($programCompositionId, $registrationPhaseId);
        $registrationPhase->update($registrationPhaseData);
        $this->registrationPhaseRepository->update();
    }

}
