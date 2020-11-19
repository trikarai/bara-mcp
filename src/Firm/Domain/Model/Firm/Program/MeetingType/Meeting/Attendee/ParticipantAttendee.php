<?php

namespace Firm\Domain\Model\Firm\Program\MeetingType\Meeting\Attendee;

use Firm\Domain\Model\Firm\Program\ {
    MeetingType\CanAttendMeeting,
    MeetingType\Meeting\Attendee,
    Participant
};

class ParticipantAttendee
{

    /**
     *
     * @var Attendee
     */
    protected $attendee;

    /**
     *
     * @var string
     */
    protected $id;

    /**
     *
     * @var Participant
     */
    protected $participant;

    function __construct(Attendee $attendee, string $id, Participant $participant)
    {
        $this->attendee = $attendee;
        $this->id = $id;
        $this->participant = $participant;
    }
    
    public function participantEquals(CanAttendMeeting $user): bool
    {
        return $this->participant === $user;
    }

}
