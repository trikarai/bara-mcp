<?php

namespace Personnel\Domain\Model\Firm\Personnel;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Personnel\Domain\Model\Firm\Personnel;
use Personnel\Domain\Model\Firm\Personnel\ProgramConsultant\ConsultantComment;
use Personnel\Domain\Model\Firm\Personnel\ProgramConsultant\ConsultationRequest;
use Personnel\Domain\Model\Firm\Personnel\ProgramConsultant\ConsultationRequestData;
use Personnel\Domain\Model\Firm\Personnel\ProgramConsultant\ConsultationSession;
use Personnel\Domain\Model\Firm\Program\Participant\Worksheet;
use Personnel\Domain\Model\Firm\Program\Participant\Worksheet\Comment;
use Resources\Domain\Model\EntityContainEvents;
use Resources\Exception\RegularException;
use Resources\Uuid;

class ProgramConsultant extends EntityContainEvents
{

    /**
     *
     * @var Personnel
     */
    protected $personnel;

    /**
     *
     * @var string
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $programId;

    /**
     *
     * @var bool
     */
    protected $active;

    /**
     *
     * @var ArrayCollection
     */
    protected $consultationRequests;

    /**
     *
     * @var ArrayCollection
     */
    protected $consultationSessions;

    protected function __construct()
    {
        
    }

    public function acceptConsultationRequest(string $consultationRequestId): void
    {
        $this->assertActive();
        
        $consultationRequest = $this->findConsultationRequestOrDie($consultationRequestId);

        $this->assertNoConsultationSessionInConflictWithConsultationRequest($consultationRequest);
        $this->assertNoOtherOfferedConsultationRequestInConflictWithConsultationRequest(
                $consultationRequest);

        $consultationRequest->accept();
        $consultationSessionId = Uuid::generateUuid4();
        $consultationSession = $consultationRequest->createConsultationSession($consultationSessionId);
        $this->consultationSessions->add($consultationSession);
        
        $firmId = $this->personnel->getFirmId();
        $personnelId = $this->personnel->getId();
        
        $this->aggregateEventFrom($consultationSession);
    }

    public function offerConsultationRequestTime(
            string $consultationRequestId, ConsultationRequestData $consultationRequestData): void
    {
        $this->assertActive();
        
        $consultationRequest = $this->findConsultationRequestOrDie($consultationRequestId);
        $consultationRequest->offer($consultationRequestData);

        $this->assertNoConsultationSessionInConflictWithConsultationRequest($consultationRequest);
        $this->assertNoOtherOfferedConsultationRequestInConflictWithConsultationRequest(
                $consultationRequest);

        $firmId = $this->personnel->getFirmId();
        $personnelId = $this->personnel->getId();
        
        $this->aggregateEventFrom($consultationRequest);
    }
    
    public function submitNewCommentOnWorksheet(
            string $consultantCommentId, Worksheet $worksheet, string $message): ConsultantComment
    {
        $this->assertActive();
        $this->assertAssetBelongsToParticipantInSameProgram($worksheet);
        
        $comment = new Comment($worksheet, $consultantCommentId, $message);
        return new ConsultantComment($this, $consultantCommentId, $comment);
    }
    
    public function submitReplyOnWorksheetComment(string $consultantCommentId, Comment $comment, string $message): ConsultantComment
    {
        $this->assertActive();
        $this->assertAssetBelongsToParticipantInSameProgram($comment);
        
        $reply = $comment->createReply($consultantCommentId, $message);
        return new ConsultantComment($this, $consultantCommentId, $reply);
    }

    protected function assertNoConsultationSessionInConflictWithConsultationRequest(
            ConsultationRequest $consultationRequest): void
    {
        $p = function (ConsultationSession $consultationSession) use ($consultationRequest) {
            return $consultationSession->intersectWithConsultationRequest($consultationRequest);
        };
        if (!empty($this->consultationSessions->filter($p)->count())) {
            $errorDetail = "forbidden: you already have consultation session at designated time";
            throw RegularException::forbidden($errorDetail);
        }
    }

    protected function assertNoOtherOfferedConsultationRequestInConflictWithConsultationRequest(
            ConsultationRequest $consultationRequest): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('concluded', false));
        $p = function (ConsultationRequest $otherConsultationRequest) use ($consultationRequest) {
            return $otherConsultationRequest->isOfferedConsultationRequestConflictedWith($consultationRequest);
        };
        if (!empty($this->consultationRequests->matching($criteria)->filter($p)->count())) {
            $errorDetail = 'forbidden: you already offer designated time in other consultation request';
            throw RegularException::forbidden($errorDetail);
        }
    }

    protected function findConsultationRequestOrDie(string $consultationRequestId): ConsultationRequest
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('id', $consultationRequestId));
        $consultationRequest = $this->consultationRequests->matching($criteria)->first();
        if (empty($consultationRequest)) {
            $errorDetail = 'not found: consultation request not found';
            throw RegularException::notFound($errorDetail);
        }
        return $consultationRequest;
    }
    
    protected function assertActive(): void
    {
        if (!$this->active) {
            $errorDetail = "forbidden: only active consultant can make this request";
            throw RegularException::forbidden($errorDetail);
        }
    }
    protected function assertAssetBelongsToParticipantInSameProgram(AssetBelongsToParticipantInProgram $asset): void
    {
        if (!$asset->belongsToParticipantInProgram($this->programId)) {
            $errorDetail = "forbidden: can only manage asset related to your program";
            throw RegularException::forbidden($errorDetail);
        }
    }

}
