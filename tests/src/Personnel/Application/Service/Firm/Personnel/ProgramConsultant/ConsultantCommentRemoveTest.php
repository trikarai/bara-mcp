<?php

namespace Personnel\Application\Service\Firm\Personnel\ProgramConsultant;

use Personnel\Domain\Model\Firm\Personnel\ProgramConsultant\ConsultantComment;
use Tests\TestBase;

class ConsultantCommentRemoveTest extends TestBase
{
    protected $service;
    protected $consultantCommentRepository, $consultantComment; 
    
    protected $firmId = 'firmId', $personnelId = 'personnelId', $programConsultationId = 'programConsultationId', 
            $consultantCommentId = 'consultantCommentId';

    protected function setUp(): void
    {
        parent::setUp();
        $this->programConsultantCompositionId = $this->buildMockOfClass(ProgramConsultantCompositionId::class);
        $this->consultantCommentRepository = $this->buildMockOfInterface(ConsultantCommentRepository::class);
        $this->consultantComment = $this->buildMockOfClass(ConsultantComment::class);
        $this->consultantCommentRepository->expects($this->any())
                ->method('ofId')
                ->with($this->firmId, $this->personnelId, $this->programConsultationId, $this->consultantCommentId)
                ->willReturn($this->consultantComment);
        
        $this->service = new ConsultantCommentRemove($this->consultantCommentRepository);
    }
    
    protected function execute()
    {
        $this->service->execute($this->firmId, $this->personnelId, $this->programConsultationId, $this->consultantCommentId);
    }
    public function test_execute_removeConsultantComment()
    {
        $this->consultantComment->expects($this->once())
                ->method('remove');
        $this->execute();
    }
    public function test_execute_updateRepository()
    {
        $this->consultantCommentRepository->expects($this->once())
                ->method('update');
        $this->execute();
    }
}
