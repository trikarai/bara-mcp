<?php

namespace App\Http\Controllers\Client\AsTeamMember\ProgramParticipation\AsMeetingInitiator;

use Firm\ {
    Application\Service\Client\AsTeamMember\ProgramParticipant\AsMeetingAttendee\UpdateMeeting,
    Domain\Model\Firm\Program\MeetingType\Meeting\Attendee,
    Domain\Model\Firm\Program\MeetingType\MeetingData,
    Domain\Model\Firm\Team\Member,
    Domain\Service\MeetingAttendeeBelongsToTeamFinder
};
use Query\ {
    Application\Service\Firm\ViewActivity,
    Domain\Model\Firm\Program\Activity
};

class MeetingController extends AsMeetingInitiatorBaseController
{

    public function update($teamId, $meetingId)
    {
        $service = $this->buildUpdateService();

        $name = $this->stripTagsInputRequest("name");
        $description = $this->stripTagsInputRequest("description");
        $startTime = $this->dateTimeImmutableOfInputRequest("startTime");
        $endTime = $this->dateTimeImmutableOfInputRequest("endTime");
        $location = $this->stripTagsInputRequest("location");
        $note = $this->stripTagsInputRequest("note");

        $meetingData = new MeetingData($name, $description, $startTime, $endTime, $location, $note);
        $service->execute($this->firmId(), $this->clientId(), $teamId, $meetingId, $meetingData);

        $viewService = $this->buildViewService();
        $meeting = $viewService->showById($this->firmId(), $meetingId);
        return $this->singleQueryResponse($this->arrayDataOfMeeting($meeting));
    }

    protected function arrayDataOfMeeting(Activity $meeting): array
    {
        return [
            "id" => $meeting->getId(),
            "name" => $meeting->getName(),
            "description" => $meeting->getDescription(),
            "startTime" => $meeting->getStartTimeString(),
            "endTime" => $meeting->getEndTimeString(),
            "location" => $meeting->getLocation(),
            "note" => $meeting->getNote(),
            "cancelled" => $meeting->isCancelled(),
            "createdTime" => $meeting->getCreatedTimeString(),
        ];
    }

    protected function buildUpdateService()
    {
        $teamMemberRepository = $this->em->getRepository(Member::class);
        $meetingAttendeeRepository = $this->em->getRepository(Attendee::class);
        $meetingAttendeeBelongsToTeamFinder = new MeetingAttendeeBelongsToTeamFinder($meetingAttendeeRepository);
        return new UpdateMeeting($teamMemberRepository, $meetingAttendeeBelongsToTeamFinder);
    }

    protected function buildViewService()
    {
        $activityRepository = $this->em->getRepository(Activity::class);
        return new ViewActivity($activityRepository);
    }

}
