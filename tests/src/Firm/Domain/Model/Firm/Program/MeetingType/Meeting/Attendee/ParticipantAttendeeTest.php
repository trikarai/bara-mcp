<?php

namespace Firm\Domain\Model\Firm\Program\MeetingType\Meeting\Attendee;

use Firm\Domain\Model\Firm\Program\ {
    MeetingType\CanAttendMeeting,
    MeetingType\Meeting\Attendee,
    Participant
};
use Tests\TestBase;

class ParticipantAttendeeTest extends TestBase
{
    protected $attendee;
    protected $participant;
    protected $participantAttendee;
    protected $id = "newId";
    protected $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->attendee = $this->buildMockOfClass(Attendee::class);
        $this->participant = $this->buildMockOfClass(Participant::class);
        $this->participantAttendee = new TestableParticipantAttendee($this->attendee, "id", $this->participant);
    }
    
    public function test_construct_setProperties()
    {
        $participantAttendee = new TestableParticipantAttendee($this->attendee, $this->id, $this->participant);
        $this->assertEquals($this->attendee, $participantAttendee->attendee);
        $this->assertEquals($this->id, $participantAttendee->id);
        $this->assertEquals($this->participant, $participantAttendee->participant);
    }
    
    public function test_participantEquals_userIsSameParticipant_returnTrue()
    {
        $this->assertTrue($this->participantAttendee->participantEquals($this->participantAttendee->participant));
    }
    public function test_participantEquals_userIsDifferentParticipant_returnFalse()
    {
        $user = $this->buildMockOfInterface(CanAttendMeeting::class);
        $this->assertFalse($this->participantAttendee->participantEquals($user));
    }
}

class TestableParticipantAttendee extends ParticipantAttendee
{
    public $attendee;
    public $id;
    public $participant;
}
