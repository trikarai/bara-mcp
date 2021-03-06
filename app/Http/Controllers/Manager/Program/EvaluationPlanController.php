<?php

namespace App\Http\Controllers\Manager\Program;

use App\Http\Controllers\Manager\ManagerBaseController;
use Firm\Application\Service\Manager\CreateEvaluationPlan;
use Firm\Application\Service\Manager\DisableEvaluationPlan;
use Firm\Application\Service\Manager\EnableEvaluationPlan;
use Firm\Application\Service\Manager\UpdateEvaluationPlan;
use Firm\Domain\Model\Firm\FeedbackForm;
use Firm\Domain\Model\Firm\Manager;
use Firm\Domain\Model\Firm\Program;
use Firm\Domain\Model\Firm\Program\EvaluationPlan as EvaluationPlan2;
use Firm\Domain\Model\Firm\Program\EvaluationPlanData;
use Firm\Domain\Model\Firm\Program\Mission;
use Query\Application\Service\Firm\Program\ViewEvaluationPlan;
use Query\Domain\Model\Firm\Program\EvaluationPlan;

class EvaluationPlanController extends ManagerBaseController
{

    public function create($programId)
    {
        $service = $this->buildCreateService();
        $feedbackFormId = $this->stripTagsInputRequest("reportFormId");
        $missionId = $this->stripTagsInputRequest('missionId');
        
        $evaluationPlanId = $service->execute(
                $this->firmId(), $this->managerId(), $programId, $this->buildEvaluationPlanData(), $feedbackFormId, 
                $missionId);

        $evaluationPlan = $this->buildViewService()->showByIdInFirm($this->firmId(), $evaluationPlanId);
        return $this->commandCreatedResponse($this->arrayDataOfEvaluationPlan($evaluationPlan));
    }

    public function update($evaluationPlanId)
    {
        $feedbackFormId = $this->stripTagsInputRequest("reportFormId");
        $missionId = $this->stripTagsInputRequest('missionId');
        
        $this->buildUpdateService()->execute(
                $this->firmId(), $this->managerId(), $evaluationPlanId, $this->buildEvaluationPlanData(), 
                $feedbackFormId, $missionId);
        
        return $this->show($evaluationPlanId);
    }

    protected function buildEvaluationPlanData()
    {
        $name = $this->stripTagsInputRequest("name");
        $interval = $this->stripTagsInputRequest("interval");
        return new EvaluationPlanData($name, $interval);
    }

    public function disable($evaluationPlanId)
    {
        $this->buildDisableService()->execute($this->firmId(), $this->managerId(), $evaluationPlanId);
        return $this->show($evaluationPlanId);
    }

    public function enable($evaluationPlanId)
    {
        $this->buildEnableService()->execute($this->firmId(), $this->managerId(), $evaluationPlanId);
        return $this->show($evaluationPlanId);
    }

    public function show($evaluationPlanId)
    {
        $evaluationPlan = $this->buildViewService()->showByIdInFirm($this->firmId(), $evaluationPlanId);
        return $this->singleQueryResponse($this->arrayDataOfEvaluationPlan($evaluationPlan));
    }

    public function showAll($programId)
    {
        $evaluationPlans = $this->buildViewService()->showAll(
                $this->firmId(), $programId, $this->getPage(), $this->getPageSize(), $enableOnly = false);
        
        $result = [];
        $result["total"] = count($evaluationPlans);
        foreach ($evaluationPlans as $evaluationPlan) {
            $result["list"][] = $this->arrayDataOfEvaluationPlan($evaluationPlan);
        }
        return $this->listQueryResponse($result);
    }

    protected function arrayDataOfEvaluationPlan(EvaluationPlan $evaluationPlan): array
    {
        return [
            "id" => $evaluationPlan->getId(),
            "name" => $evaluationPlan->getName(),
            "interval" => $evaluationPlan->getInterval(),
            "disabled" => $evaluationPlan->isDisabled(),
            "reportForm" => [
                "id" => $evaluationPlan->getReportForm()->getId(),
                "name" => $evaluationPlan->getReportForm()->getName(),
            ],
            "mission" => $this->arrayDataOfMission($evaluationPlan->getMission()),
        ];
    }
    
    protected function arrayDataOfMission(?\Query\Domain\Model\Firm\Program\Mission $mission): ?array
    {
        return empty($mission) ? null: [
            'id' => $mission->getId(),
            'name' => $mission->getName(),
        ];
    }

    protected function buildViewService()
    {
        $evaluationPlanRepository = $this->em->getRepository(EvaluationPlan::class);
        return new ViewEvaluationPlan($evaluationPlanRepository);
    }

    protected function buildCreateService()
    {
        $evaluationPlanRepository = $this->em->getRepository(EvaluationPlan2::class);
        $managerRepository = $this->em->getRepository(Manager::class);
        $programRepository = $this->em->getRepository(Program::class);
        $feedbackFormRepository = $this->em->getRepository(FeedbackForm::class);
        $missionRepository = $this->em->getRepository(Mission::class);
        
        return new CreateEvaluationPlan(
                $evaluationPlanRepository, $managerRepository, $programRepository, $feedbackFormRepository, 
                $missionRepository);
    }

    protected function buildUpdateService()
    {
        $evaluationPlanRepository = $this->em->getRepository(EvaluationPlan2::class);
        $managerRepository = $this->em->getRepository(Manager::class);
        $feedbackFormRepository = $this->em->getRepository(FeedbackForm::class);
        $missionRepository = $this->em->getRepository(Mission::class);
        
        return new UpdateEvaluationPlan(
                $evaluationPlanRepository, $managerRepository, $feedbackFormRepository, $missionRepository);
    }

    protected function buildDisableService()
    {
        $evaluationPlanRepository = $this->em->getRepository(EvaluationPlan2::class);
        $managerRepository = $this->em->getRepository(Manager::class);
        return new DisableEvaluationPlan($evaluationPlanRepository, $managerRepository);
    }

    public function buildEnableService()
    {
        $evaluationPlanRepository = $this->em->getRepository(EvaluationPlan2::class);
        $managerRepository = $this->em->getRepository(Manager::class);
        return new EnableEvaluationPlan($evaluationPlanRepository, $managerRepository);
    }

}
