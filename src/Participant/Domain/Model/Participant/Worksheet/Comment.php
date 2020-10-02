<?php

namespace Participant\Domain\Model\Participant\Worksheet;

use DateTimeImmutable;
use Participant\Domain\ {
    DependencyModel\Firm\Client\AssetBelongsToTeamInterface,
    DependencyModel\Firm\Program\Consultant\ConsultantComment,
    DependencyModel\Firm\Team,
    Model\Participant\Worksheet
};
use Resources\ {
    DateTimeImmutableBuilder,
    Exception\RegularException
};

class Comment implements AssetBelongsToTeamInterface
{

    /**
     *
     * @var Worksheet
     */
    protected $worksheet;
    
    /**
     *
     * @var Comment
     */
    protected $parent;

    /**
     *
     * @var string
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $message;

    /**
     *
     * @var DateTimeImmutable
     */
    protected $submitTime;

    /**
     *
     * @var bool
     */
    protected $removed;
    
    /**
     *
     * @var ConsultantComment||null
     */
    protected $consultantComment;

    public function __construct(Worksheet $worksheet, string $id, string $message)
    {
        $this->worksheet = $worksheet;
        $this->parent = null;
        $this->id = $id;
        $this->message = $message;
        $this->submitTime = DateTimeImmutableBuilder::buildYmdHisAccuracy();
        $this->removed = false;
    }
    
    public function createReply(string $id, string $message): self
    {
        $reply = new static($this->worksheet, $id, $message);
        $reply->parent = $this;
        return $reply;
    }

    public function remove(): void
    {
        if ($this->isConsultantComment()) {
            $errorDetail = 'forbidden: unable to remove consultant comment';
            throw RegularException::forbidden($errorDetail);
        }
        $this->removed = true;
    }
    
    public function getWorksheetId(): string
    {
        return $this->worksheet->getId();
    }
    
    public function isConsultantComment(): bool
    {
        return !empty($this->consultantComment);
    }

    public function belongsToTeam(Team $team): bool
    {
        return $this->worksheet->belongsToTeam($team);
    }

}
