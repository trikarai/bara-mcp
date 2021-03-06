<?php

namespace Query\Domain\Model\Firm\Program\Registrant;

use Query\Domain\Model\Firm\Program\ProgramsProfileForm;
use Query\Domain\Model\Firm\Program\Registrant;
use Query\Domain\Model\Shared\ContainFormRecordInterface;
use Query\Domain\Model\Shared\FormRecord;

class RegistrantProfile implements ContainFormRecordInterface
{

    /**
     * 
     * @var Registrant
     */
    protected $registrant;

    /**
     * 
     * @var string
     */
    protected $id;

    /**
     * 
     * @var ProgramsProfileForm
     */
    protected $programsProfileForm;

    /**
     * 
     * @var FormRecord
     */
    protected $formRecord;

    /**
     * 
     * @var bool
     */
    protected $removed;

    function getRegistrant(): Registrant
    {
        return $this->registrant;
    }

    function getId(): string
    {
        return $this->id;
    }

    function getProgramsProfileForm(): ProgramsProfileForm
    {
        return $this->programsProfileForm;
    }

    function isRemoved(): bool
    {
        return $this->removed;
    }

    protected function __construct()
    {
        
    }

    public function getSubmitTimeString(): ?string
    {
        return $this->formRecord->getSubmitTimeString();
    }

    public function getUnremovedAttachmentFieldRecords()
    {
        return $this->formRecord->getUnremovedAttachmentFieldRecords();
    }

    public function getUnremovedIntegerFieldRecords()
    {
        return $this->formRecord->getUnremovedIntegerFieldRecords();
    }

    public function getUnremovedMultiSelectFieldRecords()
    {
        return $this->formRecord->getUnremovedMultiSelectFieldRecords();
    }

    public function getUnremovedSingleSelectFieldRecords()
    {
        return $this->formRecord->getUnremovedSingleSelectFieldRecords();
    }

    public function getUnremovedStringFieldRecords()
    {
        return $this->formRecord->getUnremovedStringFieldRecords();
    }

    public function getUnremovedTextAreaFieldRecords()
    {
        return $this->formRecord->getUnremovedTextAreaFieldRecords();
    }

}
