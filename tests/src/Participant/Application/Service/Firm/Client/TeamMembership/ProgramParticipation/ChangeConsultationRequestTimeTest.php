<?php

namespace Participant\Application\Service\Firm\Client\TeamMembership\ProgramParticipation;

use Participant\Application\Service\Firm\Client\TeamMembership\TeamProgramParticipationRepository;
use Participant\Application\Service\Firm\Client\TeamMembershipRepository;
use Participant\Domain\DependencyModel\Firm\Client\TeamMembership;
use Participant\Domain\Model\Participant\ConsultationRequestData;
use Participant\Domain\Model\TeamProgramParticipation;
use Resources\Application\Event\Dispatcher;
use Tests\TestBase;

class ChangeConsultationRequestTimeTest extends TestBase
{

    protected $teamMembershipRepository, $teamMembership;
    protected $teamProgramParticipationRepository, $teamProgramParticipation;
    protected $dispatcher;
    protected $service;
    protected $firmId = "firmId", $clientId = "clientId", $teamMembershipId = "teamMembershipId",
            $programParticipationId = "programParticipationId", $consultationRequestId = "consultationRequestId";
    protected $consultationRequestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teamMembership = $this->buildMockOfClass(TeamMembership::class);
        $this->teamMembershipRepository = $this->buildMockOfInterface(TeamMembershipRepository::class);
        $this->teamMembershipRepository->expects($this->any())
                ->method("aTeamMembershipCorrespondWithTeam")
                ->with($this->firmId, $this->clientId, $this->teamMembershipId)
                ->willReturn($this->teamMembership);

        $this->teamProgramParticipation = $this->buildMockOfClass(TeamProgramParticipation::class);
        $this->teamProgramParticipationRepository = $this->buildMockOfInterface(TeamProgramParticipationRepository::class);
        $this->teamProgramParticipationRepository->expects($this->any())
                ->method("ofId")
                ->with($this->programParticipationId)
                ->willReturn($this->teamProgramParticipation);
        
        $this->dispatcher = $this->buildMockOfClass(Dispatcher::class);

        $this->service = new ChangeConsultationRequestTime(
                $this->teamMembershipRepository, $this->teamProgramParticipationRepository, $this->dispatcher);

        $this->consultationRequestData = $this->buildMockOfClass(ConsultationRequestData::class);
    }

    protected function execute()
    {
        $this->service->execute(
                $this->firmId, $this->clientId, $this->teamMembershipId, $this->programParticipationId,
                $this->consultationRequestId, $this->consultationRequestData);
    }
    public function test_execute_executeTeamMembershipChangeConsultationRequestTimeMethod()
    {
        $this->teamMembership->expects($this->once())
                ->method("changeConsultationRequestTime")
                ->with($this->teamProgramParticipation, $this->consultationRequestId, $this->consultationRequestData);
        $this->execute();
    }
    public function test_execute_updateTeamProgramParticipationRepository()
    {
        $this->teamProgramParticipationRepository->expects($this->once())
                ->method("update");
        $this->execute();
    }
    public function test_execute_dispatchTeamMembership()
    {
        $this->dispatcher->expects($this->once())
                ->method("dispatch")
                ->with($this->teamMembership);
        $this->execute();
    }

}
