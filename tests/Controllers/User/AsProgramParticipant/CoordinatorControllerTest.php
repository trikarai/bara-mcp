<?php

namespace Tests\Controllers\User\AsProgramParticipant;

use Tests\Controllers\RecordPreparation\Firm\ {
    Program\RecordOfCoordinator,
    RecordOfPersonnel
};

class CoordinatorControllerTest extends AsProgramParticipantTestCase
{
    protected $coordinatorUri;
    protected $coordinator;
    protected $coordinatorOne;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinatorUri = $this->asProgramParticipantUri . "/coordinators";
        
        $this->connection->table("Personnel")->truncate(); 
        $this->connection->table("Coordinator")->truncate(); 
        
        $program = $this->programParticipation->participant->program;
        $firm = $program->firm;
        
        $user = new RecordOfPersonnel($firm, 0);
        $userOne = new RecordOfPersonnel($firm, 1);
        $this->connection->table("Personnel")->insert($user->toArrayForDbEntry());
        $this->connection->table("Personnel")->insert($userOne->toArrayForDbEntry());
        
        $this->coordinator = new RecordOfCoordinator($program, $user, 0);
        $this->coordinatorOne = new RecordOfCoordinator($program, $userOne, 1);
        $this->connection->table("Coordinator")->insert($this->coordinator->toArrayForDbEntry());
        $this->connection->table("Coordinator")->insert($this->coordinatorOne->toArrayForDbEntry());
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table("Personnel")->truncate(); 
        $this->connection->table("Coordinator")->truncate(); 
    }
    
    public function test_show_200()
    {
        $response = [
            "id" => $this->coordinator->id,
            "personnel" => [
                "id" => $this->coordinator->personnel->id,
                "name" => $this->coordinator->personnel->getFullName(),
            ],
        ];
        
        $uri = $this->coordinatorUri . "/{$this->coordinator->id}";
        $this->get($uri, $this->programParticipation->user->token)
                ->seeJsonContains($response)
                ->seeStatusCode(200);
    }
    public function test_show_inactiveParticipant_403()
    {
        $uri = $this->coordinatorUri . "/{$this->coordinator->id}";
        $this->get($uri, $this->inactiveProgramParticipation->user->token)
                ->seeStatusCode(403);
    }
    
    public function test_showAll_200()
    {
        $response = [
            "total" => 2,
            "list" => [
                [
                    "id" => $this->coordinator->id,
                    "personnel" => [
                        "id" => $this->coordinator->personnel->id,
                        "name" => $this->coordinator->personnel->getFullName(),
                    ],
                ],
                [
                    "id" => $this->coordinatorOne->id,
                    "personnel" => [
                        "id" => $this->coordinatorOne->personnel->id,
                        "name" => $this->coordinatorOne->personnel->getFullName(),
                    ],
                ],
            ],
        ];
        
        $this->get($this->coordinatorUri, $this->programParticipation->user->token)
                ->seeJsonContains($response)
                ->seeStatusCode(200);
    }
    public function test_showAll_inactiveParticipant_403()
    {
        $this->get($this->coordinatorUri, $this->inactiveProgramParticipation->user->token)
                ->seeStatusCode(403);
    }
}
