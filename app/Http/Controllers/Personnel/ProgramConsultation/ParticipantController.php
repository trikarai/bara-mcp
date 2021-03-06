<?php

namespace App\Http\Controllers\Personnel\ProgramConsultation;

use Query\ {
    Application\Service\Firm\Personnel\ProgramConsultation\ViewParticipant,
    Domain\Model\Firm\Client\ClientParticipant,
    Domain\Model\Firm\Program\Participant,
    Domain\Model\Firm\Team\TeamProgramParticipation,
    Domain\Model\User\UserParticipant,
    Domain\Service\Firm\Program\ParticipantFinder
};

class ParticipantController extends ProgramConsultationBaseController
{

    public function showAll($programConsultationId)
    {
        $service = $this->buildViewService();
        $activeStatus = $this->stripTagQueryRequest("activeStatus");
        $participants = $service->showAll(
                $this->firmId(), $this->personnelId(), $programConsultationId, $this->getPage(), $this->getPageSize(),
                $activeStatus);

        $result = [];
        $result["total"] = count($participants);
        foreach ($participants as $participant) {
            $result["list"][] = $this->arrayDataOfParticipant($participant);
        }
        return $this->listQueryResponse($result);
    }

    public function show($programConsultationId, $participantId)
    {
        $service = $this->buildViewService();
        $participant = $service->showById($this->firmId(), $this->personnelId(), $programConsultationId, $participantId);
        return $this->singleQueryResponse($this->arrayDataOfParticipant($participant));
    }

    protected function arrayDataOfParticipant(Participant $participant): array
    {
        return [
            "id" => $participant->getId(),
            "enrolledTime" => $participant->getEnrolledTimeString(),
            "note" => $participant->getNote(),
            "active" => $participant->isActive(),
            "user" => $this->arrayDataOfUser($participant->getUserParticipant()),
            "client" => $this->arrayDataOfClient($participant->getClientParticipant()),
            "team" => $this->arrayDataOfTeam($participant->getTeamParticipant()),
        ];
    }

    protected function arrayDataOfUser(?UserParticipant $userParticipant): ?array
    {
        return empty($userParticipant) ? null : [
            "id" => $userParticipant->getUser()->getId(),
            "name" => $userParticipant->getUser()->getFullName(),
        ];
    }

    protected function arrayDataOfClient(?ClientParticipant $clientParticipant): ?array
    {
        return empty($clientParticipant) ? null : [
            "id" => $clientParticipant->getClient()->getId(),
            "name" => $clientParticipant->getClient()->getFullName(),
        ];
    }

    protected function arrayDataOfTeam(?TeamProgramParticipation $teamProgramParticipation): ?array
    {
        return empty($teamProgramParticipation) ? null : [
            "id" => $teamProgramParticipation->getTeam()->getId(),
            "name" => $teamProgramParticipation->getTeam()->getName(),
        ];
    }

    protected function buildViewService()
    {
        $participantRepository = $this->em->getRepository(Participant::class);
        $participantFinder = new ParticipantFinder($participantRepository);
        return new ViewParticipant($this->programConsultationRepository(), $participantFinder);
    }

}
