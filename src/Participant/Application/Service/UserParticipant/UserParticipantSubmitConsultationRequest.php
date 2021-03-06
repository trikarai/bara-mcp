<?php

namespace Participant\Application\Service\UserParticipant;

use Participant\Application\Service\Participant\ConsultationRequestRepository;
use Participant\Application\Service\UserParticipantRepository;
use Participant\Domain\Model\Participant\ConsultationRequestData;
use Resources\Application\Event\Dispatcher;

class UserParticipantSubmitConsultationRequest
{

    /**
     *
     * @var ConsultationRequestRepository
     */
    protected $consultationRequestRepository;

    /**
     *
     * @var UserParticipantRepository
     */
    protected $userParticipantRepository;

    /**
     *
     * @var ConsultationSetupRepository
     */
    protected $consultationSetupRepository;

    /**
     *
     * @var ConsultantRepository
     */
    protected $consultantRepository;

    /**
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    public function __construct(ConsultationRequestRepository $consultationRequestRepository,
            UserParticipantRepository $userParticipantRepository,
            ConsultationSetupRepository $consultationSetupRepository, ConsultantRepository $consultantRepository,
            Dispatcher $dispatcher)
    {
        $this->consultationRequestRepository = $consultationRequestRepository;
        $this->userParticipantRepository = $userParticipantRepository;
        $this->consultationSetupRepository = $consultationSetupRepository;
        $this->consultantRepository = $consultantRepository;
        $this->dispatcher = $dispatcher;
    }

    public function execute(
            string $userId, string $userParticipantId, string $consultationSetupId, string $consultantId,
            ConsultationRequestData $consultationRequestData): string
    {
        $id = $this->consultationRequestRepository->nextIdentity();
        $consultationSetup = $this->consultationSetupRepository
                ->aConsultationSetupInProgramWhereUserParticipate($userId, $userParticipantId, $consultationSetupId);
        $consultant = $this->consultantRepository
                ->aConsultantInProgramWhereUserParticipate($userId, $userParticipantId, $consultantId);
        $userParticipant = $this->userParticipantRepository->ofId($userId, $userParticipantId);
        $consultationRequest = $userParticipant->proposeConsultation($id, $consultationSetup, $consultant, $consultationRequestData);
        $this->consultationRequestRepository->add($consultationRequest);
        
        $this->dispatcher->dispatch($consultationRequest);
        
        return $id;
    }

}
