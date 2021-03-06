<?php

namespace SharedContext\Domain\ValueObject;

use Resources\BaseEnum;

class TextFieldComparisonType extends BaseEnum
{
    const EQUALS = 1;
    const LIKE = 2;
    
    public function getDisplayValue(): string
    {
        switch ($this->value) {
            case 1:
                return 'EQUALS';
            case 2:
                return 'LIKE';
            default:
                break;
        }
    }
    
    public function getComparisonQuery(string $value): string
    {
        switch ($this->value) {
            case 1:
                return "= '$value'";
            case 2:
                return "LIKE '%$value%'";
            default:
                break;
        }
    }
}
