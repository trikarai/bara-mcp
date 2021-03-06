<?php

namespace Personnel\Application\Service\Firm\Personnel\ProgramConsultant;

use Personnel\Domain\Model\Firm\Personnel\ProgramConsultant\ConsultantComment;

interface ConsultantCommentRepository
{

    public function nextIdentity(): string;

    public function add(ConsultantComment $consultantComment): void;

    public function update(): void;

    public function ofId(string $firmId, string $personnelId, string $programConsultationId, string $consultantCommentId): ConsultantComment;
}
