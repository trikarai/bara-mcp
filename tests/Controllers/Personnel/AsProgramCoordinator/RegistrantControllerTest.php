<?php

namespace Tests\Controllers\Personnel\AsProgramCoordinator;

use DateTimeImmutable;
use Tests\Controllers\RecordPreparation\ {
    Firm\Client\RecordOfClientRegistrant,
    Firm\Program\RecordOfRegistrant,
    Firm\RecordOfClient,
    RecordOfUser,
    User\RecordOfUserRegistrant
};

class RegistrantControllerTest extends AsProgramCoordinatorTestCase
{
    protected $registrantUri;
    protected $user;
    protected $registrant_user;
    protected $client;
    protected $registrantOne_client;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->registrantUri = $this->asProgramCoordinatorUri . "/registrants";
        
        $this->connection->table('User')->truncate();
        $this->connection->table('Client')->truncate();
        $this->connection->table('Registrant')->truncate();
        $this->connection->table('UserRegistrant')->truncate();
        $this->connection->table('ClientRegistrant')->truncate();
        $this->connection->table('Participant')->truncate();
        $this->connection->table('UserParticipant')->truncate();
        $this->connection->table('ClientParticipant')->truncate();
        
        $program = $this->coordinator->program;
        $firm = $program->firm;
        
        $this->user = new RecordOfUser(0);
        $this->connection->table('User')->insert($this->user->toArrayForDbEntry());
        
        $this->client = new RecordOfClient($firm, 0);
        $this->connection->table('Client')->insert($this->client->toArrayForDbEntry());
        
        $this->registrant_user = new RecordOfRegistrant($program, 0);
        $this->registrantOne_client = new RecordOfRegistrant($program, 1);
        $this->connection->table('Registrant')->insert($this->registrant_user->toArrayForDbEntry());
        $this->connection->table('Registrant')->insert($this->registrantOne_client->toArrayForDbEntry());
        
        $userRegistrant = new RecordOfUserRegistrant($this->user, $this->registrant_user);
        $this->connection->table('UserRegistrant')->insert($userRegistrant->toArrayForDbEntry());
        
        $clientRegistrant = new RecordOfClientRegistrant($this->client, $this->registrantOne_client);
        $this->connection->table('ClientRegistrant')->insert($clientRegistrant->toArrayForDbEntry());
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('User')->truncate();
        $this->connection->table('Client')->truncate();
        $this->connection->table('Registrant')->truncate();
        $this->connection->table('UserRegistrant')->truncate();
        $this->connection->table('ClientRegistrant')->truncate();
        $this->connection->table('Participant')->truncate();
        $this->connection->table('UserParticipant')->truncate();
        $this->connection->table('ClientParticipant')->truncate();
    }
    public function test_accept_201()
    {
        $response = [
            "id" => $this->registrant_user->id,
            "registeredTime" => $this->registrant_user->registeredTime,
            "note" => 'accepted',
            "concluded" => true,
            "user" => [
                "id" => $this->user->id,
                "name" => $this->user->getFullName(),
            ],
            "client" => null,
        ];
        $uri = $this->registrantUri . "/{$this->registrant_user->id}/accept";
        $this->patch($uri, [], $this->coordinator->personnel->token)
                ->seeStatusCode(200)
                ->seeJsonContains($response);
        
        $registrantEntry = [
            "id" => $this->registrant_user->id,
            "note" => 'accepted',
            "concluded" => true,
        ];
        $this->seeInDatabase("Registrant", $registrantEntry);
    }
    public function test_accept_registrationFromUser_persistUserParticipant()
    {
        $uri = $this->registrantUri . "/{$this->registrant_user->id}/accept";
        $this->patch($uri, [], $this->coordinator->personnel->token)
                ->seeStatusCode(200);
        
        $participantEntry = [
            'Program_id' => $this->registrant_user->program->id,
            'enrolledTime' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'active' => true,
            'note' => null,
        ];
        $this->seeInDatabase("Participant", $participantEntry);
        
        $userParticipantEntry = [
            "User_id" => $this->user->id,
        ];
        $this->seeInDatabase("UserParticipant", $userParticipantEntry);
    }
    public function test_accept_registrationFromClient_persistClientParticipant()
    {
        $uri = $this->registrantUri . "/{$this->registrantOne_client->id}/accept";
        $this->patch($uri, [], $this->coordinator->personnel->token)
                ->seeStatusCode(200);
        
        $participantEntry = [
            'Program_id' => $this->registrantOne_client->program->id,
            'enrolledTime' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'active' => true,
            'note' => null,
        ];
        $this->seeInDatabase("Participant", $participantEntry);
        
        $clientParticipantEntry = [
            "Client_id" => $this->client->id,
        ];
        $this->seeInDatabase("ClientParticipant", $clientParticipantEntry);
    }
    public function test_accept_requestFromNonActiveCoordinator_401()
    {
        $uri = $this->registrantUri . "/{$this->registrantOne_client->id}/accept";
        $this->patch($uri, [], $this->removedCoordinator->personnel->token)
                ->seeStatusCode(401);
    }
    
    public function test_reject_200()
    {
        $uri = $this->registrantUri . "/{$this->registrant_user->id}/reject";
        $this->patch($uri, [], $this->coordinator->personnel->token)
                ->seeStatusCode(200);
        
        $registrantEntry = [
            "id" => $this->registrant_user->id,
            "note" => 'rejected',
            "concluded" => true,
        ];
        $this->seeInDatabase("Registrant", $registrantEntry);
    }
    public function test_reject_reqeustByNonActiveCoordinator_401()
    {
        $uri = $this->registrantUri . "/{$this->registrant_user->id}/reject";
        $this->patch($uri, [], $this->removedCoordinator->personnel->token)
                ->seeStatusCode(401);
    }
    
    public function test_show_201()
    {
        $response = [
            "id" => $this->registrant_user->id,
            "registeredTime" => $this->registrant_user->registeredTime,
            "note" => $this->registrant_user->note,
            "concluded" => $this->registrant_user->concluded,
            "user" => [
                "id" => $this->user->id,
                "name" => $this->user->getFullName(),
            ],
            "client" => null,
        ];
        
        $uri = $this->registrantUri . "/{$this->registrant_user->id}";
        $this->get($uri, $this->coordinator->personnel->token)
                ->seeJsonContains($response)
                ->seeStatusCode(200);
    }
    public function test_show_requestByNonActiveCoordinator_401()
    {
        $uri = $this->registrantUri . "/{$this->registrant_user->id}";
        $this->get($uri, $this->removedCoordinator->personnel->token)
                ->seeStatusCode(401);
    }
    
    public function test_showAll_200()
    {
        $response = [
            'total' => 2,
            'list' => [
                [
                    "id" => $this->registrant_user->id,
                    "registeredTime" => $this->registrant_user->registeredTime,
                    "note" => $this->registrant_user->note,
                    "concluded" => $this->registrant_user->concluded,
                    "user" => [
                        "id" => $this->user->id,
                        "name" => $this->user->getFullName(),
                    ],
                    "client" => null,
                ],
                [
                    "id" => $this->registrantOne_client->id,
                    "registeredTime" => $this->registrantOne_client->registeredTime,
                    "note" => $this->registrantOne_client->note,
                    "concluded" => $this->registrantOne_client->concluded,
                    "client" => [
                        "id" => $this->client->id,
                        "name" => $this->client->getFullName(),
                    ],
                    "user" => null,
                ],
            ],
        ];
        $this->get($this->registrantUri, $this->coordinator->personnel->token)
                ->seeJsonContains($response)
                ->seeStatusCode(200);
    }
    public function test_showAll_requestByNonActiveCoordinator_401()
    {
        $this->get($this->registrantUri, $this->removedCoordinator->personnel->token)
                ->seeStatusCode(401);
    }
}
