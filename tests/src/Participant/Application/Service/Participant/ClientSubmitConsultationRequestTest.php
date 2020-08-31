<?php

namespace Participant\Application\Service\Participant;

use DateTimeImmutable;
use Participant\ {
    Application\Service\ClientParticipantRepository,
    Domain\Model\ClientParticipant,
    Domain\Model\DependencyEntity\Firm\Program\Consultant
};
use Resources\Application\Event\Dispatcher;
use SharedContext\Domain\Model\Firm\Program\ConsultationSetup;
use Tests\TestBase;

class ClientSubmitConsultationRequestTest extends TestBase
{

    protected $service;
    protected $consultationRequestRepository, $nextId = 'nextId';
    protected $clientParticipantRepository, $clientParticipant;
    protected $consultationSetupRepository, $consultationSetup;
    protected $consultantRepository, $consultant;
    protected $dispatcher;
    protected $firmId = 'firmId', $clientId = 'clientId', $programId = 'programId',
            $consultationSetupId = 'consultationSetupId', $personnelId = 'personnelId', $startTime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consultationRequestRepository = $this->buildMockOfClass(ConsultationRequestRepository::class);
        $this->consultationRequestRepository->expects($this->any())
                ->method('nextIdentity')
                ->willReturn($this->nextId);

        $this->clientParticipant = $this->buildMockOfClass(ClientParticipant::class);
        $this->clientParticipantRepository = $this->buildMockOfInterface(ClientParticipantRepository::class);
        $this->clientParticipantRepository->expects($this->any())
                ->method('ofId')
                ->with($this->firmId, $this->clientId, $this->programId)
                ->willReturn($this->clientParticipant);

        $this->consultationSetup = $this->buildMockOfClass(ConsultationSetup::class);
        $this->consultationSetupRepository = $this->buildMockOfInterface(ConsultationSetupRepository::class);
        $this->consultationSetupRepository->expects($this->any())
                ->method('ofId')
                ->with($this->firmId, $this->programId, $this->consultationSetupId)
                ->willReturn($this->consultationSetup);

        $this->consultant = $this->buildMockOfClass(Consultant::class);
        $this->consultantRepository = $this->buildMockOfInterface(ConsultantRepository::class);
        $this->consultantRepository->expects($this->any())
                ->method("ofId")
                ->with($this->firmId, $this->programId, $this->personnelId)
                ->willReturn($this->consultant);

        $this->dispatcher = $this->buildMockOfClass(Dispatcher::class);

        $this->service = new ClientSubmitConsultationRequest(
                $this->consultationRequestRepository, $this->clientParticipantRepository,
                $this->consultationSetupRepository, $this->consultantRepository, $this->dispatcher);

        $this->startTime = new DateTimeImmutable();
    }

    protected function execute()
    {
        return $this->service->execute(
                        $this->firmId, $this->clientId, $this->programId, $this->consultationSetupId,
                        $this->personnelId, $this->startTime);
    }
    public function test_execute_addConsultationRequestFromClientParticipantProposeConsultation()
    {
        $this->clientParticipant->expects($this->once())
                ->method('proposeConsultation')
                ->with($this->nextId, $this->consultationSetup, $this->consultant, $this->startTime);
        
        $this->consultationRequestRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
    public function test_execute_returnNextId()
    {
        $this->assertEquals($this->nextId, $this->execute());
    }
    public function test_execute_dispatchClientParticipantToDispatcher()
    {
        $this->dispatcher->expects($this->once())
                ->method('dispatch')
                ->with($this->clientParticipant);
        $this->execute();
    }

}
