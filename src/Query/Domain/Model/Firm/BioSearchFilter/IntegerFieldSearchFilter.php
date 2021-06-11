<?php

namespace Query\Domain\Model\Firm\BioSearchFilter;

use Query\Domain\Model\Firm\BioSearchFilter;
use Query\Domain\Model\Shared\Form\IntegerField;
use SharedContext\Domain\ValueObject\IntegerFieldComparisonType;

class IntegerFieldSearchFilter
{

    /**
     * 
     * @var BioSearchFilter
     */
    protected $bioSearchFilter;

    /**
     * 
     * @var string
     */
    protected $id;

    /**
     * 
     * @var bool
     */
    protected $disabled;

    /**
     * 
     * @var IntegerField
     */
    protected $integerField;

    /**
     * 
     * @var IntegerFieldComparisonType
     */
    protected $comparisonType;

    public function getBioSearchFilter(): BioSearchFilter
    {
        return $this->bioSearchFilter;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function getIntegerField(): IntegerField
    {
        return $this->integerField;
    }

    public function getComparisonTypeValue(): int
    {
        return $this->comparisonType->getValue();
    }

    protected function __construct()
    {
        
    }
    
    public function getComparisonTypeDisplayValue(): string
    {
        return $this->comparisonType->getDisplayValue();
    }

}
