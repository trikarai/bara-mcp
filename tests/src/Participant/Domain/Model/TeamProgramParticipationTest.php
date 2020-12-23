<?php

namespace Participant\Domain\Model;

use DateTimeImmutable;
use Participant\Domain\DependencyModel\Firm\Client\TeamMembership;
use Participant\Domain\DependencyModel\Firm\Program;
use Participant\Domain\DependencyModel\Firm\Program\Consultant;
use Participant\Domain\DependencyModel\Firm\Program\ConsultationSetup;
use Participant\Domain\DependencyModel\Firm\Program\Mission;
use Participant\Domain\DependencyModel\Firm\Program\ProgramsProfileForm;
use Participant\Domain\DependencyModel\Firm\Team;
use Participant\Domain\Model\Participant\ConsultationRequest;
use Participant\Domain\Model\Participant\ConsultationRequestData;
use Participant\Domain\Model\Participant\ConsultationSession;
use Participant\Domain\Model\Participant\ParticipantProfile;
use Participant\Domain\Model\Participant\Worksheet;
use Participant\Domain\Service\MetricAssignmentReportDataProvider;
use Participant\Domain\SharedModel\FileInfo;
use Resources\Domain\Event\CommonEvent;
use SharedContext\Domain\Model\SharedEntity\FormRecordData;
use Tests\TestBase;

class TeamProgramParticipationTest extends TestBase
{

    protected $teamProgramParticipation;
    protected $programParticipation;
    protected $program;
    protected $worksheetId = "worksheetId", $worksheetName = "worksheet name", $mission, $formRecordData;
    protected $worksheet;
    protected $consultationRequest, $consultationRequestId = "consultationRequestId", $consultationRequestData;
    protected $consultationSetup, $consultant;
    protected $consultationSession;
    protected $teamMember;
    protected $metricAssignmentReportDataProvider, $fileInfo;
    protected $metricAssignmentReportId = "metricAssignmentReportId", $observationTime;
    protected $programsProfileForm, $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teamProgramParticipation = new TestableTeamProgramParticipation();
        $this->teamProgramParticipation->team = $this->buildMockOfClass(Team::class);
        $this->programParticipation = $this->buildMockOfClass(Participant::class);
        $this->teamProgramParticipation->programParticipation = $this->programParticipation;

        $this->program = $this->buildMockOfClass(Program::class);

        $this->mission = $this->buildMockOfClass(Mission::class);
        $this->formRecordData = $this->buildMockOfClass(FormRecordData::class);

        $this->worksheet = $this->buildMockOfClass(Worksheet::class);

        $this->consultationRequest = $this->buildMockOfClass(ConsultationRequest::class);
        $this->consultationSetup = $this->buildMockOfClass(ConsultationSetup::class);
        $this->consultant = $this->buildMockOfClass(Consultant::class);
        $this->consultationRequestData = $this->buildMockOfClass(ConsultationRequestData::class);
        
        $this->consultationSession = $this->buildMockOfClass(ConsultationSession::class);
        
        $this->teamMember = $this->buildMockOfClass(TeamMembership::class);
        
        $this->metricAssignmentReportDataProvider = $this->buildMockOfClass(MetricAssignmentReportDataProvider::class);
        $this->fileInfo = $this->buildMockOfClass(FileInfo::class);
        $this->observationTime = new DateTimeImmutable();
        
        $this->programsProfileForm = $this->buildMockOfClass(ProgramsProfileForm::class);
        $this->profile = $this->buildMockOfClass(ParticipantProfile::class);
    }
    
    public function test_belongsToTeam_sameTeam_returnTrue()
    {
        $this->assertTrue($this->teamProgramParticipation->belongsToTeam($this->teamProgramParticipation->team));
    }
    public function test_belongsToTeam_differentTeam_returnFalse()
    {
        $team = $this->buildMockOfClass(Team::class);
        $this->assertFalse($this->teamProgramParticipation->belongsToTeam($team));
    }

    public function test_isActiveParticipantOfProgram_returnProgramParticipationsIsActiveParticipantOfProgramResult()
    {
        $this->programParticipation->expects($this->once())
                ->method("isActiveParticipantOfProgram")
                ->with($this->program)
                ->willReturn(true);
        $this->assertTrue($this->teamProgramParticipation->isActiveParticipantOfProgram($this->program));
    }

    public function test_submitRootWorksheet_returnProgramParticipationCreateRootWorksheetResult()
    {
        $this->programParticipation->expects($this->once())
                ->method("createRootWorksheet")
                ->with($this->worksheetId, $this->worksheetName, $this->mission, $this->formRecordData, $this->teamMember);
        $this->teamProgramParticipation->submitRootWorksheet(
                $this->worksheetId, $this->worksheetName, $this->mission, $this->formRecordData, $this->teamMember);
    }
    
    public function test_submitBranchWorksheet_returnParticipantsSubmitBranchWorksheetResult()
    {
        $this->programParticipation->expects($this->once())
                ->method("submitBranchWorksheet")
                ->with($this->worksheet, $this->worksheetId, $this->worksheetName, $this->mission, $this->formRecordData, $this->teamMember)
                ->willReturn($branch = $this->buildMockOfClass(Worksheet::class));
        $this->assertEquals(
                $branch, $this->teamProgramParticipation->submitBranchWorksheet(
                        $this->worksheet, $this->worksheetId, $this->worksheetName, $this->mission, 
                        $this->formRecordData, $this->teamMember));
    }

    public function test_quit_executeProgramParticipationQuitMethod()
    {
        $this->programParticipation->expects($this->once())
                ->method("quit");
        $this->teamProgramParticipation->quit();
    }

    public function test_submitConsultatioNRequest_returnProgramParticipationsSubmitConsultationRequestResult()
    {
        $this->programParticipation->expects($this->once())
                ->method("submitConsultationRequest")
                ->with($this->consultationRequestId, $this->consultationSetup, $this->consultant, $this->consultationRequestData, $this->teamMember);
        $this->teamProgramParticipation->submitConsultationRequest(
                $this->consultationRequestId, $this->consultationSetup, $this->consultant, $this->consultationRequestData, $this->teamMember);
    }
    
    public function test_changeConsultationRequestTime_executeProgramParticipationChangeConsultationRequestTime()
    {
        $this->programParticipation->expects($this->once())
                ->method("changeConsultationRequestTime")
                ->with($this->consultationRequestId, $this->consultationRequestData, $this->teamMember);
        $this->teamProgramParticipation->changeConsultationRequestTime($this->consultationRequestId, $this->consultationRequestData, $this->teamMember);
    }
    
    public function test_acceptOfferedConsultationRequest_executeProgramParticipationsAcceptOfferedConsultationRequestMethod()
    {
        $this->programParticipation->expects($this->once())
                ->method("acceptOfferedConsultationRequest")
                ->with($this->consultationRequestId, $this->anything(), $this->teamMember);
        $this->teamProgramParticipation->acceptOfferedConsultationRequest($this->consultationRequestId, $this->teamMember);
    }
    
    public function test_pullRecordedEvents_returnParticipantPullRecordedEventsResult()
    {
        $this->programParticipation->expects($this->once())
                ->method("pullRecordedEvents")
                ->willReturn($events = [$this->buildMockOfClass(CommonEvent::class)]);
        $this->assertEquals($events, $this->teamProgramParticipation->pullRecordedEvents());
    }
    
    protected function executeOwnAllAttachedFileInfo()
    {
        $this->fileInfo->expects($this->any())
                ->method("belongsToTeam")
                ->willReturn(true);
        $this->metricAssignmentReportDataProvider->expects($this->any())
                ->method("iterateAllAttachedFileInfo")
                ->willReturn([$this->fileInfo, $this->fileInfo]);
        return $this->teamProgramParticipation->ownAllAttachedFileInfo($this->metricAssignmentReportDataProvider);
    }
    public function test_ownAllAttachedFileInfo_returnTrue()
    {
        $this->assertTrue($this->executeOwnAllAttachedFileInfo());
    }
    public function test_ownAllAttachedFileInfo_FileInfoNotBelongsToUser_returnFalse()
    {
        $this->fileInfo->expects($this->once())
                ->method("belongsToTeam")
                ->with($this->teamProgramParticipation->team)
                ->willReturn(false);
        $this->assertFalse($this->executeOwnAllAttachedFileInfo());
    }
    public function test_ownAllAttachedFileInfo_containFileInfoNotBelongsToUser_returnFalse()
    {
        $this->fileInfo->expects($this->at(1))
                ->method("belongsToTeam")
                ->with($this->teamProgramParticipation->team)
                ->willReturn(false);
        $this->assertFalse($this->executeOwnAllAttachedFileInfo());
    }
    
    public function test_submitMetricAssignmentReport_returnParticipantsSubmitMetricAssignmentReportResult()
    {
        $this->programParticipation->expects($this->once())
                ->method("submitMetricAssignmentReport")
                ->with($this->metricAssignmentReportId, $this->observationTime, $this->metricAssignmentReportDataProvider);
        $this->teamProgramParticipation->submitMetricAssignmentReport(
                $this->metricAssignmentReportId, $this->observationTime, $this->metricAssignmentReportDataProvider);
    }
    
    public function test_submitProfile_submitProfileInParticipant()
    {
        $this->programParticipation->expects($this->once())
                ->method("submitProfile")
                ->with($this->programsProfileForm, $this->formRecordData);
        $this->teamProgramParticipation->submitProfile($this->programsProfileForm, $this->formRecordData);
    }
    
    public function test_removeProfile_removeProfileInParticipant()
    {
        $this->programParticipation->expects($this->once())
                ->method("removeProfile")
                ->with($this->profile);
        $this->teamProgramParticipation->removeProfile($this->profile);
    }
}

class TestableTeamProgramParticipation extends TeamProgramParticipation
{

    public $team;
    public $id;
    public $programParticipation;

    function __construct()
    {
        parent::__construct();
    }

}
