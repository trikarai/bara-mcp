<?php

namespace Firm\Application\Service\Client\ProgramParticipant;

use Firm\Domain\Model\Firm\Program\MeetingType\Meeting\Attendee;
use Tests\TestBase;

class CancelInvitationTest extends TestBase
{
    protected $attendeeRepository, $initiator, $attendee;
    protected $consultant, $consultantRepository;
    protected $service;
    protected $firmId = "firmId", $clientId = "clientId", $meetingId = "meetingId", $attendeeId = "attendeeId";

    protected function setUp(): void
    {
        parent::setUp();
        $this->initiator = $this->buildMockOfClass(Attendee::class);
        $this->attendee = $this->buildMockOfClass(Attendee::class);
        $this->attendeeRepository = $this->buildMockOfInterface(AttendeeRepository::class);
        $this->attendeeRepository->expects($this->any())
                ->method("anAttendeeBelongsToClientParticipantCorrespondWithMeeting")
                ->with($this->firmId, $this->clientId, $this->meetingId)
                ->willReturn($this->initiator);
        $this->attendeeRepository->expects($this->any())
                ->method("ofId")
                ->with($this->attendeeId)
                ->willReturn($this->attendee);
        
        $this->service = new CancelInvitation($this->attendeeRepository);
    }
    
    protected function execute()
    {
        $this->service->execute($this->firmId, $this->clientId, $this->meetingId, $this->attendeeId);
    }
    public function test_execute_executeInitiatorsCancelInvitation()
    {
        $this->initiator->expects($this->once())
                ->method("cancelInvitationTo")
                ->with($this->attendee);
        $this->execute();
    }
    public function test_execute_updateRepository()
    {
        $this->attendeeRepository->expects($this->once())
                ->method("update");
        $this->execute();
    }
}
