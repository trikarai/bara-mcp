<?php

namespace Firm\Application\Service\Coordinator;

class ApproveObjectiveProgressReport
{

    /**
     * 
     * @var CoordinatorRepository
     */
    protected $coordinatorRepository;

    /**
     * 
     * @var ObjectiveProgressReportRepository
     */
    protected $objectiveProgressReportRepository;
    
    public function __construct(CoordinatorRepository $coordinatorRepository,
            ObjectiveProgressReportRepository $objectiveProgressReportRepository)
    {
        $this->coordinatorRepository = $coordinatorRepository;
        $this->objectiveProgressReportRepository = $objectiveProgressReportRepository;
    }
    
    public function execute(string $firmId, string $personnelId, string $programId, string $objectiveProgressReportId): void
    {
        $objectiveProgressReport = $this->objectiveProgressReportRepository->ofId($objectiveProgressReportId);
        $this->coordinatorRepository->aCoordinatorCorrespondWithProgram($firmId, $personnelId, $programId)
                ->approveObjectiveProgressReport($objectiveProgressReport);
        $this->coordinatorRepository->update();
    }

}
