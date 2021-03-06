<?php

namespace Query\Domain\Model\Firm;

use Query\Domain\Model\Firm;

class Program
{

    /**
     *
     * @var Firm
     */
    protected $firm;

    /**
     *
     * @var string
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string||null
     */
    protected $description = null;

    /**
     *
     * @var ParticipantTypes
     */
    protected $participantTypes;

    /**
     *
     * @var bool
     */
    protected $published = false;

    /**
     * 
     * @var bool
     */
    protected $strictMissionOrder;

    /**
     *
     * @var bool
     */
    protected $removed = false;

    function getFirm(): Firm
    {
        return $this->firm;
    }

    function getId(): string
    {
        return $this->id;
    }

    function getName(): string
    {
        return $this->name;
    }

    function getDescription(): ?string
    {
        return $this->description;
    }

    function isPublished(): bool
    {
        return $this->published;
    }

    public function isStrictMissionOrder(): bool
    {
        return $this->strictMissionOrder;
    }

    function isRemoved(): bool
    {
        return $this->removed;
    }

    protected function __construct()
    {
        
    }

    public function getParticipantTypeValues(): array
    {
        return $this->participantTypes->getValues();
    }

}
