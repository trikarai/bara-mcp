<?php

namespace Firm\Domain\Model\Firm\Program;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Firm\Domain\Model\Firm\Client;
use Firm\Domain\Model\Firm\Program;
use Firm\Domain\Model\Firm\Program\ConsultationSetup\ConsultationRequest;
use Firm\Domain\Model\Firm\Program\ConsultationSetup\ConsultationSession;
use Firm\Domain\Model\Firm\Program\MeetingType\Meeting\Attendee;
use Firm\Domain\Model\Firm\Program\MeetingType\Meeting\Attendee\ParticipantAttendee;
use Firm\Domain\Model\Firm\Program\MeetingType\MeetingData;
use Firm\Domain\Model\Firm\Program\Participant\DedicatedMentor;
use Firm\Domain\Model\Firm\Program\Participant\Evaluation;
use Firm\Domain\Model\Firm\Program\Participant\EvaluationData;
use Firm\Domain\Model\Firm\Program\Participant\MetricAssignment;
use Firm\Domain\Model\Firm\Program\Participant\ParticipantProfile;
use Firm\Domain\Model\Firm\Team;
use Firm\Domain\Model\User;
use Firm\Domain\Service\MetricAssignmentDataProvider;
use Resources\DateTimeImmutableBuilder;
use SharedContext\Domain\Model\SharedEntity\FormRecord;
use SharedContext\Domain\ValueObject\ActivityParticipantType;
use Tests\TestBase;

class ParticipantTest extends TestBase
{

    protected $program;
    protected $participant;
    protected $asset;
    protected $consultationRequest;
    protected $consultationSession;
    protected $invitation;
    protected $inactiveParticipant;
    protected $clientParticipant;
    protected $userParticipant;
    protected $teamParticipant;
    protected $id = 'newParticipantId', $user, $client, $teamId = "teamId";
    protected $registrant;
    protected $metricAssignment;
    protected $metricAssignmentDataProvider;
    protected $metric;
    protected $meetingId = "meetingId", $meetingType, $meetingData;
    protected $team;
    protected $evaluation;
    protected $evaluationPlan, $coordinator, $evaluationData;
    protected $programsProfileForm, $formRecord;
    protected $consultant;
    protected $dedicatedMentor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->program = $this->buildMockOfClass(Program::class);

        $this->participant = new TestableParticipant($this->program, 'id');
        $this->participant->consultationRequests = new ArrayCollection();
        $this->participant->consultationSessions = new ArrayCollection();
        $this->participant->meetingInvitations = new ArrayCollection();
        $this->participant->dedicatedMentors = new ArrayCollection();
        
        $this->consultationRequest = $this->buildMockOfClass(ConsultationRequest::class);
        $this->participant->consultationRequests->add($this->consultationRequest);
        $this->consultationSession = $this->buildMockOfClass(ConsultationSession::class);
        $this->participant->consultationSessions->add($this->consultationSession);
        $this->invitation = $this->buildMockOfClass(ParticipantAttendee::class);
        $this->participant->meetingInvitations->add($this->invitation);
        $this->dedicatedMentor = $this->buildMockOfClass(DedicatedMentor::class);
        $this->participant->dedicatedMentors->add($this->dedicatedMentor);
        
        $this->participant->evaluations = new ArrayCollection();
        
        $this->asset = $this->buildMockOfInterface(AssetInProgram::class);
        
        $this->inactiveParticipant = new TestableParticipant($this->program, 'id');
        $this->inactiveParticipant->active = false;
        $this->inactiveParticipant->note = 'booted';
        
        $this->user = $this->buildMockOfClass(User::class);
        $this->client = $this->buildMockOfClass(Client::class);

        $this->clientParticipant = $this->buildMockOfClass(ClientParticipant::class);
        $this->participant->clientParticipant = $this->clientParticipant;

        $this->userParticipant = $this->buildMockOfClass(UserParticipant::class);
        $this->inactiveParticipant->userParticipant = $this->userParticipant;

        $this->registrant = $this->buildMockOfClass(Registrant::class);

        $this->teamParticipant = $this->buildMockOfClass(TeamParticipant::class);

        $this->metricAssignment = $this->buildMockOfClass(MetricAssignment::class);
        $this->metricAssignmentDataProvider = $this->buildMockOfClass(MetricAssignmentDataProvider::class);
        $this->metricAssignmentDataProvider->expects($this->any())->method("getStartDate")->willReturn(new DateTimeImmutable("+1 days"));
        $this->metricAssignmentDataProvider->expects($this->any())->method("getEndDate")->willReturn(new DateTimeImmutable("+2 days"));
        
        $this->metric = $this->buildMockOfClass(Metric::class);
        
        $this->meetingType = $this->buildMockOfClass(ActivityType::class);
        $this->meetingData = $this->buildMockOfClass(MeetingData::class);
        
        $this->team = $this->buildMockOfClass(Team::class);
        
        $this->evaluation = $this->buildMockOfClass(Evaluation::class);
        $this->participant->evaluations->add($this->evaluation);
        $this->evaluationPlan = $this->buildMockOfClass(EvaluationPlan::class);
        $this->evaluationData = $this->buildMockOfClass(EvaluationData::class);
        $this->evaluationData->expects($this->any())->method("getStatus")->willReturn("pass");
        $this->coordinator = $this->buildMockOfClass(Coordinator::class);
        
        $this->programsProfileForm = $this->buildMockOfClass(ProgramsProfileForm::class);
        $this->formRecord = $this->buildMockOfClass(FormRecord::class);
        
        $this->consultant = $this->buildMockOfClass(Consultant::class);
    }
    protected function assertInactiveParticipant(callable $operation): void
    {
        $this->assertRegularExceptionThrowed($operation, 'Forbidden', 'forbidden: inactive partiicpant');
    }

    public function test_participantForUser_setProperties()
    {
        $participant = TestableParticipant::participantForUser($this->program, $this->id, $this->user);
        $this->assertEquals($this->program, $participant->program);
        $this->assertEquals($this->id, $participant->id);
        $this->assertEquals(DateTimeImmutableBuilder::buildYmdHisAccuracy(), $participant->enrolledTime);
        $this->assertTrue($participant->active);
        $this->assertNull($participant->note);

        $userParticipant = new UserParticipant($participant, $this->id, $this->user);
        $this->assertEquals($userParticipant, $participant->userParticipant);
        $this->assertNull($participant->clientParticipant);
    }

    public function test_participantForClient_setProperties()
    {
        $participant = TestableParticipant::participantForClient($this->program, $this->id, $this->client);
        $this->assertEquals($this->program, $participant->program);
        $this->assertEquals($this->id, $participant->id);
        $this->assertEquals(DateTimeImmutableBuilder::buildYmdHisAccuracy(), $participant->enrolledTime);
        $this->assertTrue($participant->active);
        $this->assertNull($participant->note);

        $clientParticipant = new ClientParticipant($participant, $this->id, $this->client);
        $this->assertEquals($clientParticipant, $participant->clientParticipant);
        $this->assertNull($participant->userParticipant);
    }

    public function test_participantForTeam_setProperties()
    {
        $participant = TestableParticipant::participantForTeam($this->program, $this->id, $this->teamId);
        $this->assertEquals($this->program, $participant->program);
        $this->assertEquals($this->id, $participant->id);
        $this->assertEquals(DateTimeImmutableBuilder::buildYmdHisAccuracy(), $participant->enrolledTime);
        $this->assertTrue($participant->active);
        $this->assertNull($participant->note);

        $teamParticipant = new TeamParticipant($participant, $this->id, $this->teamId);
        $this->assertEquals($teamParticipant, $participant->teamParticipant);
        $this->assertNull($participant->userParticipant);
        $this->assertNull($participant->clientParticipant);
    }
    
    public function test_asserActive_activeParticipant_void()
    {
        $this->participant->assertActive();
        $this->markAsSuccess();
    }
    public function test_asserActive_inactiveParticipant_forbidden()
    {
        $this->participant->active = false;
        $this->assertInactiveParticipant(function(){
            $this->participant->assertActive();
        });
    }
    
    public function test_assertAssetAccessible_inaccesibleAsset_forbidden()
    {
        $this->assertRegularExceptionThrowed(function (){
            $this->participant->assertAssetAccessible($this->asset);
        }, 'Forbidden', 'forbidden: unable to access asset not in same program');
    }
    public function test_assertAssetAccessible_accessibleAsset_void()
    {
        $this->asset->expects($this->once())
                ->method('belongsToProgram')
                ->with($this->participant->program)
                ->willReturn(true);
        $this->participant->assertAssetAccessible($this->asset);
    }

    public function test_belongsToProgram_sameProgram_returnTrue()
    {
        $this->assertTrue($this->participant->belongsToProgram($this->participant->program));
    }
    public function test_belongsToProgram_differentprogram_returnFalse()
    {
        $program = $this->buildMockOfClass(Program::class);
        $this->assertFalse($this->participant->belongsToProgram($program));
    }

    protected function executeReenroll()
    {
        $this->inactiveParticipant->reenroll();
    }
    public function test_reenroll_setActiveTrueAndNulledNote()
    {
        $this->executeReenroll();
        $this->assertTrue($this->inactiveParticipant->active);
        $this->assertNull($this->inactiveParticipant->note);
    }
    public function test_reenroll_activeParticipant_forbiddenError()
    {
        $operation = function () {
            $this->participant->reenroll();
        };
        $errorDetail = 'forbidden: already active participant';
        $this->assertRegularExceptionThrowed($operation, 'Forbidden', $errorDetail);
    }

    protected function executeCorrespondWithRegistrant()
    {
        return $this->participant->correspondWithRegistrant($this->registrant);
    }
    public function test_correspondWithRegistrant_returnClientParticipantsCorrespondWithRegistrantResult()
    {
        $this->clientParticipant->expects($this->once())
                ->method('correspondWithRegistrant');
        $this->executeCorrespondWithRegistrant();
    }
    public function test_correspondWithRegistrant_aUserParticipant_returnUserParticipantCorrespondWithRegistrantRegsult()
    {
        $this->participant->clientParticipant = null;
        $this->participant->userParticipant = $this->userParticipant;

        $this->userParticipant->expects($this->once())
                ->method('correspondWithRegistrant');
        $this->executeCorrespondWithRegistrant();
    }
    public function test_correspondWithRegistrant_aTeamParticipant_returnTeamParticipantCorrespondWithRegistrantResult()
    {
        $this->participant->clientParticipant = null;
        $this->participant->teamParticipant = $this->teamParticipant;

        $this->teamParticipant->expects($this->once())
                ->method("correspondWithRegistrant");
        $this->executeCorrespondWithRegistrant();
    }

    protected function executeAssignMetrics()
    {
        $this->participant->assignMetrics($this->metricAssignmentDataProvider);
    }
    public function test_assignMetric_addMetricAssignment()
    {
        $this->executeAssignMetrics();
        $this->assertInstanceOf(MetricAssignment::class, $this->participant->metricAssignment);
    }
    public function test_assignMetric_alreadyHasMetricAssignment_updateExistingMetricAssignment()
    {
        $this->participant->metricAssignment = $this->metricAssignment;
        $this->metricAssignment->expects($this->once())
                ->method("update")
                ->with($this->metricAssignmentDataProvider);
        $this->executeAssignMetrics();
    }
    public function test_assignMetric_alreadyHasMetricAssignment_avoidAddNewAssignment()
    {
        $this->participant->metricAssignment = $this->metricAssignment;
        $this->executeAssignMetrics();
        $this->assertEquals($this->metricAssignment, $this->participant->metricAssignment);
    }
    
    public function test_belongsInTheSameProgramAs_returnMetricsBelongsToProgramResult()
    {
        $this->metric->expects($this->once())
                ->method("belongsToProgram");
        $this->participant->belongsInTheSameProgramAs($this->metric);
    }
    
    public function test_canInvolvedInProgram_sameProgram_returnTrue()
    {
        $this->assertTrue($this->participant->canInvolvedInProgram($this->participant->program));
    }
    public function test_canInvolvedInProgram_inactiveParticipant_returnFalse()
    {
        $this->participant->active = false;
        $this->assertFalse($this->participant->canInvolvedInProgram($this->participant->program));
    }
    public function test_canInvolvedInProgram_differentProgram_returnFalse()
    {
        $program = $this->buildMockOfClass(Program::class);
        $this->assertFalse($this->participant->canInvolvedInProgram($program));
    }
    
    public function test_roleCorrespondWith_returnActivityParticipantTypeIsParticipantResult()
    {
        $activityParticipantType = $this->buildMockOfClass(ActivityParticipantType::class);
        $activityParticipantType->expects($this->once())
                ->method("isParticipantType");
        $this->participant->roleCorrespondWith($activityParticipantType);
    }
    
    public function test_registerAsAttendeeCandidate_setParticipantAsAttendeeCandidate()
    {
        $attendee = $this->buildMockOfClass(Attendee::class);
        $attendee->expects($this->once())
                ->method("setParticipantAsAttendeeCandidate")
                ->with($this->participant);
        $this->participant->registerAsAttendeeCandidate($attendee);
    }
    
    protected function executeInitiateMeeting()
    {
        $this->meetingType->expects($this->any())
                ->method("belongsToProgram")
                ->willReturn(true);
        $this->participant->initiateMeeting($this->meetingId, $this->meetingType, $this->meetingData);
    }
    public function test_initiateMeeting_returnMeetingTypeCreateMeetingResult()
    {
        $this->meetingType->expects($this->once())
                ->method("createMeeting")
                ->with($this->meetingId, $this->meetingData, $this->participant);
        $this->executeInitiateMeeting();
    }
    public function test_initiateMeeting_inactiveParticipant_forbidden()
    {
        $this->participant->active = false;
        $operation = function (){
            $this->executeInitiateMeeting();
        };
        $errorDetail = "forbidden: only active participant can make this request";
        $this->assertRegularExceptionThrowed($operation, "Forbidden", $errorDetail);
    }
    public function test_initiateMeeting_meetingTypeNotFromInProgram_forbidden()
    {
        $this->meetingType->expects($this->once())
                ->method("belongsToProgram")
                ->with($this->program)
                ->willReturn(false);
        $operation = function (){
            $this->executeInitiateMeeting();
        };
        $errorDetail = "forbidden: can only manage meeting type on same program";
        $this->assertRegularExceptionThrowed($operation, "Forbidden", $errorDetail);
    }
    
    public function test_belongsToTeam_returnTeamParticipantBelongsToTeamResult()
    {
        $this->participant->teamParticipant = $this->teamParticipant;
        $this->teamParticipant->expects($this->once())
                ->method("belongsToTeam")
                ->with($this->team);
        $this->participant->belongsToTeam($this->team);
    }
    public function test_belongsToTeam_notATeamParticipant_returnFalse()
    {
        $this->participant->teamParticipant = null;
        $this->assertFalse($this->participant->belongsToTeam($this->team));
    }
    
    protected function executeReceiveEvaluation()
    {
        $this->participant->receiveEvaluation($this->evaluationPlan, $this->evaluationData, $this->coordinator);
    }
    public function test_receiveEvaluation_addEvaluationToCollection()
    {
        $this->executeReceiveEvaluation();
        $this->assertEquals(2, $this->participant->evaluations->count());
        $this->assertInstanceOf(Evaluation::class, $this->participant->evaluations->last());
    }
    public function test_receiveEvaluation_inactiveParticipant_forbidden()
    {
        $this->participant->active = false;
        $operation = function (){
            $this->executeReceiveEvaluation();
        };
        $errorDetail = "forbidden: unable to evaluate inactive participant";
        $this->assertRegularExceptionThrowed($operation, "Forbidden", $errorDetail);
    }
    public function test_receiveEvaluation_alreadyReceiveConcludedEvaluationForSamePlan_forbidden()
    {
        $this->evaluation->expects($this->once())
                ->method("isCompletedEvaluationForPlan")
                ->with($this->evaluationPlan)
                ->willReturn(true);
        $operation = function (){
            $this->executeReceiveEvaluation();
        };
        $errorDetail = "forbidden: participant already completed evaluation for this plan";
        $this->assertRegularExceptionThrowed($operation, "Forbidden", $errorDetail);
    }
    
    protected function executeQualify()
    {
        $this->participant->qualify();
    }
    public function test_qualify_setInactiveAndNoteQualified()
    {
        $this->executeQualify();
        $this->assertFalse($this->participant->active);
        $this->assertEquals("completed", $this->participant->note);
    }
    public function test_qualify_inactiveParticipant_forbidden()
    {
        $this->participant->active = false;
        $operation = function (){
            $this->executeQualify();
        };
        $errorDetail = "forbidden: unable to qualify inactive participant";
        $this->assertRegularExceptionThrowed($operation, "Forbidden", $errorDetail);
    }
    
    protected function executeDisable()
    {
        $this->participant->disable();
    }
    public function test_disable_setInactiveAndNote()
    {
        $this->executeDisable();
        $this->assertFalse($this->participant->active);
        $this->assertEquals("fail", $this->participant->note);
    }
    public function test_disable_alreadyInactive_forbidden()
    {
        $this->participant->active = false;
        $operation = function (){
            $this->executeDisable();
        };
        $errorDetail = "forbidden: unable to disable inactive participant";
        $this->assertRegularExceptionThrowed($operation, "Forbidden", $errorDetail);
    }
    public function test_disable_disableUpcomingConsultationSession()
    {
        $this->consultationSession->expects($this->once())
                ->method("disableUpcomingSession");
        $this->executeDisable();
    }
    public function test_disable_disableUpcomingConsultationRequest()
    {
        $this->consultationRequest->expects($this->once())
                ->method("disableUpcomingRequest");
        $this->executeDisable();
    }
    public function test_disable_disableValidInvitation()
    {
        $this->invitation->expects($this->once())
                ->method("disableValidInvitation");
        $this->executeDisable();
    }
    
    public function test_addProfile_addProfileToCollection()
    {
        $this->formRecord->expects($this->once())
                ->method("getId")->willReturn($formRecordId = "formRecordId");
        $profile = new ParticipantProfile($this->participant, $formRecordId, $this->programsProfileForm, $this->formRecord);
        
        $this->participant->addProfile($this->programsProfileForm, $this->formRecord);
        $this->assertEquals($profile, $this->participant->profiles->first());
    }
    
    protected function executeDedicateMentor()
    {
        $this->consultant->expects($this->any())
                ->method('isActive')
                ->willReturn(true);
        return $this->participant->dedicateMentor($this->consultant);
    }
    public function test_dedicateMentor_addDedicatedMentorToCollection()
    {
        $this->executeDedicateMentor();
        $this->assertEquals(2, $this->participant->dedicatedMentors->count());
        $this->assertInstanceOf(DedicatedMentor::class, $this->participant->dedicatedMentors->last());
    }
    public function test_dedicateMentor_consultantAlreadyParticipantDedicatedMentor_reassignExistingDedicatedMentor()
    {
        $this->dedicatedMentor->expects($this->once())
                ->method('consultantEquals')
                ->with($this->consultant)
                ->willReturn(true);
        $this->dedicatedMentor->expects($this->once())
                ->method('reassign');
        $this->executeDedicateMentor();
    }
    public function test_dedicateMentor_alreadyADedicatedMentor_preventAddNewDedicatedMentorToCollection()
    {
        $this->dedicatedMentor->expects($this->once())
                ->method('consultantEquals')
                ->with($this->consultant)
                ->willReturn(true);
        $this->executeDedicateMentor();
        $this->assertEquals(1, $this->participant->dedicatedMentors->count());
    }
    public function test_dedicatementor_returnDedicatedMentorId()
    {
        $this->dedicatedMentor->expects($this->once())
                ->method('consultantEquals')
                ->with($this->consultant)
                ->willReturn(true);
        $this->dedicatedMentor->expects($this->once())->method('getId')
                ->willReturn($dedicatedMentorId = 'dedicatedMentorId');
        $this->assertEquals($dedicatedMentorId, $this->executeDedicateMentor());
    }
    public function test_dedicateMentor_inactiveParticipant_forbidden()
    {
        $this->participant->active = false;
        $this->assertInactiveParticipant(function (){
            $this->executeDedicateMentor();
        });
    }
}

class TestableParticipant extends Participant
{

    public $program;
    public $id;
    public $enrolledTime;
    public $active = true;
    public $note;
    public $clientParticipant;
    public $userParticipant;
    public $teamParticipant;
    public $metricAssignment;
    public $evaluations;
    public $profiles;
    public $meetingInvitations;
    public $consultationRequests;
    public $consultationSessions;
    public $dedicatedMentors;

    public function __construct(Program $program, string $id)
    {
        parent::__construct($program, $id);
    }

}
