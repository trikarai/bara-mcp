<?php

namespace Bara\Domain\Model\Firm;

use Bara\Domain\Model\Firm;
use DateTimeImmutable;
use Resources\{
    Domain\ValueObject\Password,
    ValidationRule,
    ValidationService
};

class Manager
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
     * @var string
     */
    protected $email;

    /**
     *
     * @var Password
     */
    protected $password;

    /**
     *
     * @var string
     */
    protected $phone;

    /**
     *
     * @var DateTimeImmutable
     */
    protected $joinTime;

    /**
     *
     * @var bool
     */
    protected $removed = false;

    private function setName($name)
    {
        $errorDetail = 'bad request: manager name is required';
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($name, $errorDetail);
        $this->name = $name;
    }

    private function setEmail($email)
    {
        $errorDetail = 'bad request: manager email is required and must be in valid email format';
        ValidationService::build()
                ->addRule(ValidationRule::email())
                ->execute($email, $errorDetail);
        $this->email = $email;
    }

    private function setPhone($phone)
    {
        $errorDetail = 'bad request: manager phone must be in valid phone format';
        ValidationService::build()
                ->addRule(ValidationRule::optional(ValidationRule::phone()))
                ->execute($phone, $errorDetail);
        $this->phone = $phone;
    }

    function __construct(Firm $firm, string $id, ManagerData $managerData)
    {
        $this->firm = $firm;
        $this->id = $id;
        $this->setName($managerData->getName());
        $this->setEmail($managerData->getEmail());
        $this->password = new Password($managerData->getPassword());
        $this->setPhone($managerData->getPhone());
        $this->joinTime = new DateTimeImmutable();
        $this->removed = false;
    }

}
