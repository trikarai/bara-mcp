<?php

namespace Tests\Controllers\Client\TeamMembership\ProgramParticipation;

use Tests\Controllers\ {
    Client\TeamMembership\ProgramParticipationTestCase,
    RecordPreparation\Firm\Program\Participant\ConsultationRequest\RecordOfConsultationRequestActivityLog,
    RecordPreparation\Firm\Program\Participant\ConsultationSession\RecordOfConsultationSessionActivityLog,
    RecordPreparation\Firm\Program\Participant\RecordOfConsultationRequest,
    RecordPreparation\Firm\Program\Participant\RecordOfConsultationSession,
    RecordPreparation\Firm\Program\Participant\RecordOfWorksheet,
    RecordPreparation\Firm\Program\Participant\Worksheet\RecordOfWorksheetActivityLog,
    RecordPreparation\Firm\Program\RecordOfConsultant,
    RecordPreparation\Firm\Program\RecordOfConsultationSetup,
    RecordPreparation\Firm\Program\RecordOfMission,
    RecordPreparation\Firm\RecordOfFeedbackForm,
    RecordPreparation\Firm\RecordOfPersonnel,
    RecordPreparation\Firm\RecordOfWorksheetForm,
    RecordPreparation\Firm\Team\Member\RecordOfTeamMemberActivityLog,
    RecordPreparation\Shared\RecordOfActivityLog,
    RecordPreparation\Shared\RecordOfForm,
    RecordPreparation\Shared\RecordOfFormRecord
};

class ActivityLogControllerTest extends ProgramParticipationTestCase
{
    protected $activityLogUri;
    protected $activityLog;
    protected $consultationRequest;
    protected $activityLogOne;
    protected $consultationSession;
    protected $activityLogTwo;
    protected $worksheet;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->activityLogUri = $this->programParticipationUri . "/{$this->programParticipation->id}/activity-logs";
        
        $this->connection->table("ActivityLog")->truncate();
        $this->connection->table("TeamMemberActivityLog")->truncate();
        $this->connection->table("ConsultationRequestActivityLog")->truncate();
        $this->connection->table("ConsultationSessionActivityLog")->truncate();
        $this->connection->table("WorksheetActivityLog")->truncate();
        $this->connection->table("Personnel")->truncate();
        $this->connection->table("Form")->truncate();
        $this->connection->table("FeedbackForm")->truncate();
        $this->connection->table("ConsultationSetup")->truncate();
        $this->connection->table("Consultant")->truncate();
        $this->connection->table("ConsultationRequest")->truncate();
        $this->connection->table("ConsultationSession")->truncate();
        $this->connection->table("FormRecord")->truncate();
        $this->connection->table("WorksheetForm")->truncate();
        $this->connection->table("Mission")->truncate();
        $this->connection->table("Worksheet")->truncate();
        
        $participant = $this->programParticipation->participant;
        $program = $participant->program;
        $firm = $program->firm;
        
        $this->activityLog = new RecordOfActivityLog(0);
        $this->activityLogOne = new RecordOfActivityLog(1);
        $this->activityLogTwo = new RecordOfActivityLog(2);
        $this->connection->table("ActivityLog")->insert($this->activityLog->toArrayForDbEntry());
        $this->connection->table("ActivityLog")->insert($this->activityLogOne->toArrayForDbEntry());
        $this->connection->table("ActivityLog")->insert($this->activityLogTwo->toArrayForDbEntry());
        
        $teamMemberActivityLog = new RecordOfTeamMemberActivityLog($this->teamMembership, $this->activityLog);
        $teamMemberActivityLogOne = new RecordOfTeamMemberActivityLog($this->teamMembership, $this->activityLogOne);
        $teamMemberActivityLogTwo = new RecordOfTeamMemberActivityLog($this->teamMembership, $this->activityLogTwo);
        $this->connection->table("TeamMemberActivityLog")->insert($teamMemberActivityLog->toArrayForDbEntry());
        $this->connection->table("TeamMemberActivityLog")->insert($teamMemberActivityLogOne->toArrayForDbEntry());
        $this->connection->table("TeamMemberActivityLog")->insert($teamMemberActivityLogTwo->toArrayForDbEntry());
        
        
        $personnel = new RecordOfPersonnel($firm, 0, 'personnel@email.org', 'password213');
        $this->connection->table("Personnel")->insert($personnel->toArrayForDbEntry());
        
        $form = new RecordOfForm(0);
        $this->connection->table("Form")->insert($form->toArrayForDbEntry());
        
        $feedbackForm = new RecordOfFeedbackForm($firm, $form);
        $this->connection->table("FeedbackForm")->insert($feedbackForm->toArrayForDbEntry());
        
        $consultationSetup = new RecordOfConsultationSetup($program, $feedbackForm, $feedbackForm, 0);
        $this->connection->table("ConsultationSetup")->insert($consultationSetup->toArrayForDbEntry());
        
        
        $consultant = new RecordOfConsultant($program, $personnel, 0);
        $this->connection->table("Consultant")->insert($consultant->toArrayForDbEntry());
        
        $this->consultationRequest = new RecordOfConsultationRequest($consultationSetup, $participant, $consultant, 0);
        $this->connection->table("ConsultationRequest")->insert($this->consultationRequest->toArrayForDbEntry());
        
        $consultationRequestActivityLog = new RecordOfConsultationRequestActivityLog($this->consultationRequest, $this->activityLog);
        $this->connection->table("ConsultationRequestActivityLog")->insert($consultationRequestActivityLog->toArrayForDbEntry());
        
        $this->consultationSession = new RecordOfConsultationSession($consultationSetup, $participant, $consultant, 0);
        $this->connection->table("ConsultationSession")->insert($this->consultationSession->toArrayForDbEntry());
        
        $consultationSessionActivityLogOne = new RecordOfConsultationSessionActivityLog($this->consultationSession, $this->activityLogOne);
        $this->connection->table("ConsultationSessionActivityLog")->insert($consultationSessionActivityLogOne->toArrayForDbEntry());
        
        $formRecord = new RecordOfFormRecord($form, 0);
        $this->connection->table("FormRecord")->insert($formRecord->toArrayForDbEntry());
        
        $worksheetForm = new RecordOfWorksheetForm($firm, $form);
        $this->connection->table("WorksheetForm")->insert($worksheetForm->toArrayForDbEntry());
        
        $mission = new RecordOfMission($program, $worksheetForm, 0, null);
        $this->connection->table("Mission")->insert($mission->toArrayForDbEntry());
        
        $this->worksheet = new RecordOfWorksheet($participant, $formRecord, $mission, 0);
        $this->connection->table("Worksheet")->insert($this->worksheet->toArrayForDbEntry());
        
        $worksheetActivityLog = new RecordOfWorksheetActivityLog($this->worksheet, $this->activityLogTwo);
        $this->connection->table("WorksheetActivityLog")->insert($worksheetActivityLog->toArrayForDbEntry());
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table("ActivityLog")->truncate();
        $this->connection->table("TeamMemberActivityLog")->truncate();
        $this->connection->table("ConsultationRequestActivityLog")->truncate();
        $this->connection->table("ConsultationSessionActivityLog")->truncate();
        $this->connection->table("WorksheetActivityLog")->truncate();
        $this->connection->table("Personnel")->truncate();
        $this->connection->table("Form")->truncate();
        $this->connection->table("FeedbackForm")->truncate();
        $this->connection->table("ConsultationSetup")->truncate();
        $this->connection->table("Consultant")->truncate();
        $this->connection->table("ConsultationRequest")->truncate();
        $this->connection->table("ConsultationSession")->truncate();
        $this->connection->table("Worksheet")->truncate();
        $this->connection->table("FormRecord")->truncate();
        $this->connection->table("WorksheetForm")->truncate();
        $this->connection->table("Mission")->truncate();
    }
    
    public function test_showAll_200()
    {
        $response = [
            "total" => 3,
            "list" => [
                [
                    "id" => $this->activityLog->id,
                    "message" => $this->activityLog->message,
                    "occuredTime" => $this->activityLog->occuredTime,
                    "teamMember" => [
                        "id" => $this->teamMembership->id,
                        "client" => [
                            "id" => $this->teamMembership->client->id,
                            "name" => $this->teamMembership->client->getFullName(),
                        ],
                    ],
                    "consultationRequest" => [
                        "id" => $this->consultationRequest->id,
                    ],
                    "consultationSession" => null,
                    "worksheet" => null,
                ],
                [
                    "id" => $this->activityLogOne->id,
                    "message" => $this->activityLogOne->message,
                    "occuredTime" => $this->activityLogOne->occuredTime,
                    "teamMember" => [
                        "id" => $this->teamMembership->id,
                        "client" => [
                            "id" => $this->teamMembership->client->id,
                            "name" => $this->teamMembership->client->getFullName(),
                        ],
                    ],
                    "consultationRequest" => null,
                    "consultationSession" => [
                        "id" => $this->consultationSession->id,
                    ],
                    "worksheet" => null,
                ],
                [
                    "id" => $this->activityLogTwo->id,
                    "message" => $this->activityLogTwo->message,
                    "occuredTime" => $this->activityLogTwo->occuredTime,
                    "teamMember" => [
                        "id" => $this->teamMembership->id,
                        "client" => [
                            "id" => $this->teamMembership->client->id,
                            "name" => $this->teamMembership->client->getFullName(),
                        ],
                    ],
                    "consultationRequest" => null,
                    "consultationSession" => null,
                    "worksheet" => [
                        "id" => $this->worksheet->id,
                    ],
                ],
            ],
        ];
        $this->get($this->activityLogUri, $this->teamMembership->client->token)
                ->seeJsonContains($response)
                ->seeStatusCode(200);
    }
}
 