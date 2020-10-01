<?php

namespace Participant\Domain\DependencyModel\Firm\Client;

use DateTimeImmutable;
use Participant\Domain\{
    DependencyModel\Firm\Client,
    DependencyModel\Firm\Program,
    DependencyModel\Firm\Program\Consultant,
    DependencyModel\Firm\Program\ConsultationSetup,
    DependencyModel\Firm\Program\Mission,
    DependencyModel\Firm\Team,
    Model\Participant\ConsultationRequest,
    Model\Participant\Worksheet,
    Model\TeamProgramParticipation,
    Model\TeamProgramRegistration
};
use Resources\Exception\RegularException;
use SharedContext\Domain\Model\SharedEntity\FormRecordData;

class TeamMembership
{

    /**
     *
     * @var Client
     */
    protected $client;

    /**
     *
     * @var string
     */
    protected $id;

    /**
     *
     * @var Team
     */
    protected $team;

    /**
     *
     * @var bool
     */
    protected $active;

    protected function __construct()
    {
        
    }

    protected function assertTeamProgramParticipationBelongsToSameTeam(TeamProgramParticipation $teamProgramParticipation): void
    {
        if (!$teamProgramParticipation->teamEquals($this->team)) {
            $errorDetail = "forbbiden: not allowed to manage asset of other team";
            throw RegularException::forbidden($errorDetail);
        }
    }

    protected function assertActive(): void
    {
        if (!$this->active) {
            $errorDetail = "forbidden: only active team member can make this request";
            throw RegularException::forbidden($errorDetail);
        }
    }

    public function registerTeamToProgram(string $teamProgramRegistrationId, Program $program): TeamProgramRegistration
    {
        $this->assertActive();
        return $this->team->registerToProgram($teamProgramRegistrationId, $program);
    }

    public function cancelTeamprogramRegistration(TeamProgramRegistration $teamProgramRegistration): void
    {
        $this->assertActive();
        if (!$teamProgramRegistration->teamEquals($this->team)) {
            $errorDetail = "forbidden: unable to alter registration from other team";
            throw RegularException::forbidden($errorDetail);
        }
        $teamProgramRegistration->cancel();
    }

    public function quitTeamProgramParticipation(TeamProgramParticipation $teamProgramParticipation): void
    {
        $this->assertActive();
        $this->assertTeamProgramParticipationBelongsToSameTeam($teamProgramParticipation);
        $teamProgramParticipation->quit();
    }

    public function submitRootWorksheet(
            TeamProgramParticipation $teamProgramParticipation, string $worksheetId, string $name, Mission $mission,
            FormRecordData $formRecordData): Worksheet
    {
        $this->assertActive();
        $this->assertTeamProgramParticipationBelongsToSameTeam($teamProgramParticipation);
        return $teamProgramParticipation->submitRootWorksheet($worksheetId, $name, $mission, $formRecordData);
    }

    public function submitBranchWorksheet(
            TeamProgramParticipation $teamProgramParticipation, Worksheet $parentWorksheet, string $branchWorksheetId,
            string $branchWorksheetName, Mission $mission, FormRecordData $formRecordData): Worksheet
    {
        $this->assertActive();
        $this->assertTeamProgramParticipationBelongsToSameTeam($teamProgramParticipation);
        return $teamProgramParticipation->submitBranchWorksheet(
                        $parentWorksheet, $branchWorksheetId, $branchWorksheetName, $mission, $formRecordData);
    }

    public function updateWorksheet(
            TeamProgramParticipation $teamProgramParticipation, Worksheet $worksheet, string $worksheetName,
            FormRecordData $formRecordData): void
    {
        $this->assertActive();
        $this->assertTeamProgramParticipationBelongsToSameTeam($teamProgramParticipation);
        $teamProgramParticipation->updateWorksheet($worksheet, $worksheetName, $formRecordData);
    }

    public function submitConsultationRequest(
            TeamProgramParticipation $teamProgramParticipation, string $consultationRequestId,
            ConsultationSetup $consultationSetup, Consultant $consultant, DateTimeImmutable $startTime): ConsultationRequest
    {
        $this->assertActive();
        $this->assertTeamProgramParticipationBelongsToSameTeam($teamProgramParticipation);
        return $teamProgramParticipation->submitConsultationRequest(
                        $consultationRequestId, $consultationSetup, $consultant, $startTime);
    }

    public function changeConsultationRequestTime(
            TeamProgramParticipation $teamProgramParticipation, string $consultationRequestId,
            DateTimeImmutable $startTime): void
    {
        $this->assertActive();
        $this->assertTeamProgramParticipationBelongsToSameTeam($teamProgramParticipation);
        $teamProgramParticipation->changeConsultationRequestTime($consultationRequestId, $startTime);
    }

    public function cancelConsultationRequest(
            TeamProgramParticipation $teamProgramParticipation, ConsultationRequest $consultationRequest): void
    {
        $this->assertActive();
        $this->assertTeamProgramParticipationBelongsToSameTeam($teamProgramParticipation);
        $teamProgramParticipation->cancelConsultationRequest($consultationRequest);
    }

    public function acceptOfferedConsultationRequest(TeamProgramParticipation $teamProgramParticipation,
            string $consultationRequestId): void
    {
        $this->assertActive();
        $this->assertTeamProgramParticipationBelongsToSameTeam($teamProgramParticipation);
        $teamProgramParticipation->acceptOfferedConsultationRequest($consultationRequestId);
    }

}
