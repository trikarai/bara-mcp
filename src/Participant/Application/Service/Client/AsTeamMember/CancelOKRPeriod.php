<?php

namespace Participant\Application\Service\Client\AsTeamMember;

use Participant\Application\Service\Participant\OKRPeriodRepository;

class CancelOKRPeriod
{

    /**
     * 
     * @var TeamMemberRepository
     */
    protected $teamMemberRepository;

    /**
     * 
     * @var TeamParticipantRepository
     */
    protected $teamParticipantRepository;

    /**
     * 
     * @var OKRPeriodRepository
     */
    protected $okrPeriodRepository;

    public function __construct(TeamMemberRepository $teamMemberRepository,
            TeamParticipantRepository $teamParticipantRepository, OKRPeriodRepository $okrPeriodRepository)
    {
        $this->teamMemberRepository = $teamMemberRepository;
        $this->teamParticipantRepository = $teamParticipantRepository;
        $this->okrPeriodRepository = $okrPeriodRepository;
    }

    public function execute(string $firmId, string $clientId, string $teamId, string $participantId, string $okrPeriodId): void
    {
        $teamParticipant = $this->teamParticipantRepository->ofId($participantId);
        $okrPeriod = $this->okrPeriodRepository->ofId($okrPeriodId);
        $this->teamMemberRepository->aTeamMembershipCorrespondWithTeam($firmId, $clientId, $teamId)
                ->cancelOKRPeriod($teamParticipant, $okrPeriod);
        $this->teamMemberRepository->update();
    }

}
