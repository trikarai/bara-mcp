<?php

namespace App\Http\Controllers\Personnel\ProgramConsultant;

use App\Http\Controllers\{
    FormRecordDataBuilder,
    FormRecordToArrayDataConverter,
    Personnel\PersonnelBaseController
};
use Personnel\{
    Application\Service\Firm\Personnel\PersonnelCompositionId,
    Application\Service\Firm\Personnel\ProgramConsultant\ConsultationSession\ConsultantFeedbackSet,
    Application\Service\Firm\Personnel\ProgramConsultant\ConsultationSessionView,
    Application\Service\Firm\Personnel\ProgramConsultant\ProgramConsultantCompositionId,
    Domain\Model\Firm\Personnel\PersonnelFileInfo,
    Domain\Model\Firm\Personnel\ProgramConsultant\ConsultationSession,
    Domain\Service\PersonnelFileInfoFinder
};

class ConsultationSessionController extends PersonnelBaseController
{

    public function setConsultantFeedback($programConsultantId, $consultationSessionId)
    {
        $service = $this->buildSetConsultantFeedbackService();
        $programConsultantCompositionId = new ProgramConsultantCompositionId(
                $this->firmId(), $this->personnelId(), $programConsultantId);

        $personnelFileInfoRepository = $this->em->getRepository(PersonnelFileInfo::class);
        $personnelCompositionId = new PersonnelCompositionId($this->firmId(), $this->personnelId());

        $fileInfoFinder = new PersonnelFileInfoFinder($personnelFileInfoRepository, $personnelCompositionId);
        $formRecordData = (new FormRecordDataBuilder($this->request, $fileInfoFinder))->build();
        $service->execute($programConsultantCompositionId, $consultationSessionId, $formRecordData);
        
        return $this->show($programConsultantId, $consultationSessionId);
    }

    public function show($programConsultantId, $consultationSessionId)
    {
        $service = $this->buildViewService();
        $programConsultantCompositionId = new ProgramConsultantCompositionId(
                $this->firmId(), $this->personnelId(), $programConsultantId);
        $consultationSession = $service->showById($programConsultantCompositionId, $consultationSessionId);

        return $this->singleQueryResponse($this->arrayDataOfConsultationSession($consultationSession));
    }

    public function showAll($programConsultantId)
    {
        $service = $this->buildViewService();
        $programConsultantCompositionId = new ProgramConsultantCompositionId(
                $this->firmId(), $this->personnelId(), $programConsultantId);
        $consultationSessions = $service->showAll(
                $programConsultantCompositionId, $this->getPage(), $this->getPageSize());

        $result = [];
        $result['total'] = count($consultationSessions);
        foreach ($consultationSessions as $consultationSession) {
            $result['list'][] = [
                "id" => $consultationSession->getId(),
                "startTime" => $consultationSession->getStartTimeString(),
                "endTime" => $consultationSession->getEndTimeString(),
                "participant" => [
                    "id" => $consultationSession->getParticipant()->getId(),
                    "client" => [
                        "id" => $consultationSession->getParticipant()->getClient()->getId(),
                        "name" => $consultationSession->getParticipant()->getClient()->getName(),
                    ],
                ],
                "hasConsultantFeedback" => !empty($consultationSession->getConsultantFeedback()),
            ];
        }
        return $this->listQueryResponse($result);
    }

    protected function arrayDataOfConsultationSession(ConsultationSession $consultationSession): array
    {
        $consultantFeedbackData = empty($consultationSession->getConsultantFeedback()) ? null :
                (new FormRecordToArrayDataConverter())->convert($consultationSession->getConsultantFeedback());
        return [
            "id" => $consultationSession->getId(),
            "startTime" => $consultationSession->getStartTimeString(),
            "endTime" => $consultationSession->getEndTimeString(),
            "consultationSetup" => [
                "id" => $consultationSession->getConsultationSetup()->getId(),
                "name" => $consultationSession->getConsultationSetup()->getName()
            ],
            "participant" => [
                "id" => $consultationSession->getParticipant()->getId(),
                "client" => [
                    "id" => $consultationSession->getParticipant()->getClient()->getId(),
                    "name" => $consultationSession->getParticipant()->getClient()->getName(),
                ],
            ],
            "consultantFeedback" => $consultantFeedbackData,
        ];
    }

    protected function buildSetConsultantFeedbackService()
    {
        $consultationSessionRepository = $this->em->getRepository(ConsultationSession::class);
        return new ConsultantFeedbackSet($consultationSessionRepository);
    }

    protected function buildViewService()
    {
        $consultationSessionRepository = $this->em->getRepository(ConsultationSession::class);
        return new ConsultationSessionView($consultationSessionRepository);
    }

}
