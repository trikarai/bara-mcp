<?php

namespace App\Http\Controllers\Client\AsProgramParticipant\Mission;

use App\Http\Controllers\Client\AsProgramParticipant\AsProgramParticipantBaseController;
use Config\EventList;
use Participant\{
    Application\Listener\LearningMaterialAccessedByParticipantListener,
    Application\Service\Participant\LogViewLearningMaterialActivity,
    Domain\Model\Participant,
    Domain\Model\Participant\ViewLearningMaterialActivityLog
};
use Query\{
    Application\Service\Firm\Client\AsProgramParticipant\ViewLearningMaterialDetail,
    Application\Service\Firm\Program\Mission\ViewLearningMaterial,
    Domain\Model\Firm\Client\ClientParticipant,
    Domain\Model\Firm\Program\Mission\LearningMaterial,
    Domain\Service\LearningMaterialFinder
};
use Resources\Application\Event\Dispatcher;

class LearningMaterialController extends AsProgramParticipantBaseController
{

    public function show($programId, $missionId, $learningMaterialId)
    {
        $service = $this->buildViewDetailService();
        $learningMaterial = $service->execute($this->clientId(), $programId, $learningMaterialId);

        return $this->singleQueryResponse($this->arrayDataOfLearningMaterial($learningMaterial));
    }

    public function showAll($programId, $missionId)
    {
        $this->authorizedClientIsActiveProgramParticipant($programId);

        $service = $this->buildViewService();
        $learningMaterials = $service->showAll($this->firmId(), $programId, $missionId, $this->getPage(),
                $this->getPageSize());
        
        return $this->commonIdNameListQueryResponse($learningMaterials);
    }

    protected function arrayDataOfLearningMaterial(LearningMaterial $learningMaterial): array
    {
        return [
            "id" => $learningMaterial->getId(),
            "name" => $learningMaterial->getName(),
            "content" => $learningMaterial->getContent(),
        ];
    }

    protected function buildViewService()
    {
        $learningMaterialRepository = $this->em->getRepository(LearningMaterial::class);
        return new ViewLearningMaterial($learningMaterialRepository);
    }

    protected function buildViewDetailService()
    {
        $clientProgramParticipationRepository = $this->em->getRepository(ClientParticipant::class);
        $learningMaterialRepository = $this->em->getRepository(LearningMaterial::class);
        $learningMaterialFinder = new LearningMaterialFinder($learningMaterialRepository);
        $dispatcher = new Dispatcher();
        $dispatcher->addListener(
                EventList::LEARNING_MATERIAL_VIEWED_BY_PARTICIPANT,
                $this->buildLearningMaterialAccessedByParticipantListener());

        return new ViewLearningMaterialDetail(
                $clientProgramParticipationRepository, $learningMaterialFinder, $dispatcher);
    }

    protected function buildLearningMaterialAccessedByParticipantListener()
    {
        $viewLearningMaterialActivityLogRepository = $this->em->getRepository(ViewLearningMaterialActivityLog::class);
        $participantRepository = $this->em->getRepository(Participant::class);
        $logViewLearningMaterialActivity = new LogViewLearningMaterialActivity($viewLearningMaterialActivityLogRepository,
                $participantRepository);
        return new LearningMaterialAccessedByParticipantListener($logViewLearningMaterialActivity);
    }

}
