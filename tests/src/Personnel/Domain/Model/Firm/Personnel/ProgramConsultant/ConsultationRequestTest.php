<?php

namespace Personnel\Domain\Model\Firm\Personnel\ProgramConsultant;

use DateTimeImmutable;
use Personnel\Domain\Model\Firm\ {
    Personnel\ProgramConsultant,
    Program\ConsultationSetup,
    Program\Participant
};
use Resources\Domain\ValueObject\DateTimeInterval;
use SharedContext\Domain\Model\SharedEntity\ConsultationRequestStatusVO;
use Tests\TestBase;

class ConsultationRequestTest extends TestBase
{

    protected $consultationSetup, $participant, $programConsultant;
    protected $startEndTime;
    protected $consultationRequest;
    protected $startTime;
    protected $otherConsultationRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->startEndTime = $this->buildMockOfClass(DateTimeInterval::class);
        $this->consultationSetup = $this->buildMockOfClass(ConsultationSetup::class);
        
        $this->participant = $this->buildMockOfClass(Participant::class);
        $this->programConsultant = $this->buildMockOfClass(ProgramConsultant::class);

        $this->consultationRequest = new TestableConsultationRequest();
        $this->consultationRequest->consultationSetup = $this->consultationSetup;
        $this->consultationRequest->id = 'negotiate-schedule-id';
        $this->consultationRequest->participant = $this->participant;
        $this->consultationRequest->programConsultant = $this->programConsultant;
        $this->consultationRequest->startEndTime = $this->startEndTime;
        $this->consultationRequest->status = new ConsultationRequestStatusVO('proposed');

        $this->startTime = new DateTimeImmutable('+1 days');
        $this->otherConsultationRequest = $this->buildMockOfClass(ConsultationRequest::class);
    }

    protected function executeReject()
    {
        $this->consultationRequest->reject();
    }

    public function test_reject_setStatusRejected()
    {
        $this->executeReject();
        $status = new ConsultationRequestStatusVO("rejected");
        $this->assertEquals($status, $this->consultationRequest->status);
    }

    public function test_reject_setConcludedFlagTrue()
    {
        $this->executeReject();
        $this->assertTrue($this->consultationRequest->concluded);
    }

    public function test_reject_alreadyConcluded_throwEx()
    {
        $this->consultationRequest->concluded = true;
        $operation = function () {
            $this->executeReject();
        };
        $errorDetail = 'forbidden: consultation request already concluded';
        $this->assertRegularExceptionThrowed($operation, 'Forbidden', $errorDetail);
    }

    protected function executeOffer()
    {
        $this->consultationRequest->offer($this->startTime);
    }

    public function test_offer_setStatusOffered()
    {
        $this->executeOffer();
        $status = new ConsultationRequestStatusVO("offered");
        $this->assertEquals($status, $this->consultationRequest->status);
    }

    public function test_offer_alreadyConcluded_throwEx()
    {
        $this->consultationRequest->concluded = true;
        $operation = function () {
            $this->executeOffer();
        };
        $errorDetail = 'forbidden: consultation request already concluded';
        $this->assertRegularExceptionThrowed($operation, 'Forbidden', $errorDetail);
    }

    public function test_offer_setStartEndTimeFromConsultationSetup()
    {
        $this->consultationSetup->expects($this->once())
                ->method('getSessionStartEndTimeOf')
                ->with($this->startTime)
                ->willReturn($this->startEndTime);
        $this->executeOffer();
        $this->assertEquals($this->startEndTime, $this->consultationRequest->startEndTime);
    }

    public function test_offer_startEndTimeUnavailableInParticipantsSchedule_throwEx()
    {
        $this->participant->expects($this->once())
                ->method('hasConsultationSessionInConflictWithConsultationRequest')
                ->with($this->consultationRequest)
                ->willReturn(true);
        $operation = function () {
            $this->executeOffer();
        };
        $errorDetail = 'forbidden: consultation request time in conflict with participan existing consultation session';
        $this->assertRegularExceptionThrowed($operation, 'Forbidden', $errorDetail);
    }

    protected function executeAccept()
    {
        $this->consultationRequest->accept();
    }

    public function test_accept_alreadyConcluded_throwEx()
    {
        $this->consultationRequest->concluded = true;
        $operation = function () {
            $this->executeAccept();
        };
        $errorDetail = 'forbidden: consultation request already concluded';
        $this->assertRegularExceptionThrowed($operation, 'Forbidden', $errorDetail);
    }

    public function test_accept_setStatusScheduled()
    {
        $this->executeAccept();
        $status = new ConsultationRequestStatusVO("scheduled");
        $this->assertEquals($status, $this->consultationRequest->status);
    }

    public function test_accept_setConcludedFlagTrue()
    {
        $this->executeAccept();
        $this->assertTrue($this->consultationRequest->concluded);
    }
    
    public function test_accept_currentStatusNotProposed_error403()
    {
        $this->consultationRequest->status = new ConsultationRequestStatusVO('offered');
        $operation = function (){
            $this->executeAccept();
        };
        $errorDetail = "forbidden: can only accept proposed consultation request";
        $this->assertRegularExceptionThrowed($operation, 'Forbidden', $errorDetail);
    }

    public function test_createConsultationSetupSchedule_returnConsultationSetupSchedule()
    {
        $id = 'id';
        $consultationSession = new ConsultationSession(
                $this->programConsultant, $id, $this->participant, $this->consultationSetup, $this->startEndTime);
        $this->assertEquals($consultationSession, $this->consultationRequest->createConsultationSession($id));
    }

    protected function executeIsOfferedConsultationRequestConflictedWith()
    {
        return $this->consultationRequest->isOfferedConsultationRequestConflictedWith($this->otherConsultationRequest);
    }
    public function test_isOfferedConsultaionRequestConflictedWith_noConflict_returnFalse()
    {
        $this->assertFalse($this->executeIsOfferedConsultationRequestConflictedWith());
    }
    public function test_isOfferedConsultationRequestConflictedWith_timeIntersectWithOther_returnTrue()
    {
        $this->consultationRequest->status = new ConsultationRequestStatusVO('offered');
        $this->startEndTime->expects($this->once())
                ->method('intersectWith')
                ->willReturn(true);
        $this->assertTrue($this->executeIsOfferedConsultationRequestConflictedWith());
    }
    public function test_isOfferedConsultationRequestConflictedWith_statusNotOffered_returnFalse()
    {
        $this->startEndTime->expects($this->any())
                ->method('intersectWith')
                ->willReturn(true);
        $this->assertFalse($this->executeIsOfferedConsultationRequestConflictedWith());
    }
    public function test_isOfferedConsultationRequestConflictedWith_comparedToSelf_returnFalse()
    {
        $this->consultationRequest->status = new ConsultationRequestStatusVO('offered');
        $this->startEndTime->expects($this->any())
                ->method('intersectWith')
                ->willReturn(true);
        $this->assertFalse($this->consultationRequest->isOfferedConsultationRequestConflictedWith($this->consultationRequest));
    }
}

class TestableConsultationRequest extends ConsultationRequest
{

    public $consultationSetup, $id, $participant, $programConsultant, $startEndTime, $concluded = false, $status;

    function __construct()
    {
        parent::__construct();
    }

}
