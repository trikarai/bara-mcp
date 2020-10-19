<?php

namespace App\Http\Controllers\Client\AsTeamMember;

use App\Http\Controllers\Client\ClientBaseController;
use Query\ {
    Application\Auth\Firm\Team\TeamAdminAuthorization,
    Application\Auth\Firm\Team\TeamMemberAuthorization,
    Domain\Model\Firm\Team\Member
};

class AsTeamMemberBaseController extends ClientBaseController
{
    protected function authorizeClientIsActiveTeamMember(string $teamId): void
    {
        $memberRepository = $this->em->getRepository(Member::class);
        $authZ = new TeamMemberAuthorization($memberRepository);
        $authZ->execute($this->firmId(), $teamId, $this->clientId());
    }
    
    protected function authorizeClientIsActiveTeamMemberWithAdminPriviledge(string $teamId): void
    {
        $memberRepository = $this->em->getRepository(Member::class);
        $authZ = new TeamAdminAuthorization($memberRepository);
        $authZ->execute($this->firmId(), $teamId, $this->clientId());
    }
}
