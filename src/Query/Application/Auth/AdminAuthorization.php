<?php

namespace Query\Application\Auth;

use Resources\Exception\RegularException;

class AdminAuthorization
{
    protected $adminRepository;
    
    function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }
    
    public function execute(string $adminId): void
    {
        if (!$this->adminRepository->containRecordOfId($adminId)) {
            $errorDetail = 'unauthorized: only admin can make this request';
            throw RegularException::unauthorized($errorDetail);
        }
        
    }

}
