<?php

namespace Query\Application\Service\Firm;

use Query\Domain\Model\Firm\Manager;
use Resources\Exception\RegularException;

class ManagerLogin
{

    /**
     *
     * @var ManagerRepository
     */
    protected $managerRepository;

    function __construct(ManagerRepository $managerRepository)
    {
        $this->managerRepository = $managerRepository;
    }

    public function execute(string $firmIdentifier, string $email, string $password): Manager
    {
        $errorDetail = "unauthorized: invalid email or password";
        try {
            $manager = $this->managerRepository->ofEmail($firmIdentifier, $email);
            if (!$manager->passwordMatches($password)) {
                throw RegularException::unauthorized($errorDetail);
            }
        } catch (RegularException $ex) {
            throw RegularException::unauthorized($errorDetail);
        }
        return $manager;
    }

}
