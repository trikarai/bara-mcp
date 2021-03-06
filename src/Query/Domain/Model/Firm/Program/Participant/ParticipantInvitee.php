<?php

namespace Query\Domain\Model\Firm\Program\Participant;

use Query\Domain\Model\Firm\Program\ {
    Activity,
    Activity\Invitee,
    Activity\Invitee\InviteeReport,
    ActivityType\ActivityParticipant,
    Participant
};

class ParticipantInvitee
{

    /**
     *
     * @var Participant
     */
    protected $participant;

    /**
     *
     * @var string
     */
    protected $id;

    /**
     *
     * @var Invitee
     */
    protected $invitee;

    function getParticipant(): Participant
    {
        return $this->participant;
    }

    function getId(): string
    {
        return $this->id;
    }

    protected function __construct()
    {
        
    }

    function getActivity(): Activity
    {
        return $this->invitee->getActivity();
    }

    function getActivityParticipant(): ActivityParticipant
    {
        return $this->invitee->getActivityParticipant();
    }

    function isWillAttend(): ?bool
    {
        return $this->invitee->isWillAttend();
    }

    function isAttended(): ?bool
    {
        return $this->invitee->isAttended();
    }

    function isAnInitiator(): bool
    {
        return $this->invitee->isAnInitiator();
    }

    function isCancelled(): bool
    {
        return $this->invitee->isCancelled();
    }

    function getReport(): ?InviteeReport
    {
        return $this->invitee->getReport();
    }

}
