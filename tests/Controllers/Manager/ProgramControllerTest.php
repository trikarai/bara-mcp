<?php

namespace Tests\Controllers\Manager;

use Tests\Controllers\RecordPreparation\Firm\RecordOfProgram;

class ProgramControllerTest extends ProgramTestCase
{
    protected $programOne;
    protected $programInput = [
        "name" => "new program name",
        "description" => "new program description",
        "strictMissionOrder" => true,
        "participantTypes" => [
            'client', 'user'
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->programOne = new RecordOfProgram($this->firm, 1);
        $this->programOne->published = false;
        $this->connection->table('Program')->insert($this->programOne->toArrayForDbEntry());
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
    }
    
    public function test_add()
    {
        $response = [
            "name" => $this->programInput['name'],
            "description" => $this->programInput['description'],
            "participantTypes" => $this->programInput['participantTypes'],
            "strictMissionOrder" => $this->programInput['strictMissionOrder'],
            "published" => false,
        ];
        $this->post($this->programUri, $this->programInput, $this->manager->token)
            ->seeStatusCode(201)
            ->seeJsonContains($response);
        
        $programRecord = [
            "Firm_id" => $this->firm->id,
            "name" => $this->programInput['name'],
            "description" => $this->programInput['description'],
            "strictMissionOrder" => $this->programInput['strictMissionOrder'],
            "participantTypes" => "client,user",
            "published" => false,
            "removed" => false,
        ];
        $this->seeInDatabase('Program', $programRecord);
    }
    public function test_add_userNotManager_error401()
    {
        $this->post($this->programUri, $this->programInput, $this->removedManager->token)
            ->seeStatusCode(401);
    }
    
    public function test_update()
    {
        $response = [
            "id" => $this->program->id,
            "name" => $this->programInput['name'],
            "description" => $this->programInput['description'],
            "participantTypes" => $this->programInput['participantTypes'],
            "published" => $this->program->published,
        ];
        
        $uri = "{$this->programUri}/{$this->program->id}/update";
        $this->patch($uri, $this->programInput, $this->manager->token)
            ->seeStatusCode(200)
            ->seeJsonContains($response);
        
        $programmeRecord = [
            "id" => $this->program->id,
            "name" => $this->programInput['name'],
            "description" => $this->programInput['description'],
            "participantTypes" => "client,user",
            "published" => $this->program->published,
            "removed" => false,
        ];
        $this->seeInDatabase('Program', $programmeRecord);
    }
    public function test_update_userNotManager_error401()
    {
        $uri = "{$this->programUri}/{$this->program->id}/update";
        $this->patch($uri, $this->programInput, $this->removedManager->token)
            ->seeStatusCode(401);
    }
    
    public function test_publish()
    {
        $response = [
            "id" => $this->program->id,
            "published" => true,
        ];
        
        $uri = "{$this->programUri}/{$this->program->id}/publish";
        $this->patch($uri, [], $this->manager->token)
            ->seeStatusCode(200)
            ->seeJsonContains($response);
        
        $programmeRecord = [
            "id" => $this->program->id,
            "published" => true,
            "removed" => false,
        ];
        $this->seeInDatabase('Program', $programmeRecord);
        
    }
    public function test_publish_userNotManager_error401()
    {
        $uri = "{$this->programUri}/{$this->program->id}/publish";
        $this->patch($uri, [], $this->removedManager->token)
            ->seeStatusCode(401);
    }
    
    public function test_remove()
    {
        $uri = "{$this->programUri}/{$this->programOne->id}";
        $this->delete($uri, [], $this->manager->token)
            ->seeStatusCode(200);
        
        $programmeRecord = [
            "id" => $this->programOne->id,
            "removed" => true,
        ];
        $this->seeInDatabase('Program', $programmeRecord);
    }
    public function test_remove_userNotManager_error403()
    {
        $uri = "{$this->programUri}/{$this->programOne->id}";
        $this->delete($uri, [], $this->removedManager->token)
            ->seeStatusCode(401);
    }
    
    public function test_show()
    {
        $response = [
            "id" => $this->program->id,
            "name" => $this->program->name,
            "description" => $this->program->description,
            "strictMissionOrder" => $this->program->strictMissionOrder,
            "published" => $this->program->published,
        ];
        $uri = "{$this->programUri}/{$this->program->id}";
        $this->get($uri, $this->manager->token)
            ->seeStatusCode(200)
            ->seeJsonContains($response);
    }
    public function test_show_usetNotManager_error401()
    {
        $uri = "{$this->programUri}/{$this->program->id}";
        $this->get($uri, $this->removedManager->token)
            ->seeStatusCode(401);
    }
    
    public function test_showAll()
    {
        $response = [
            "total" => 2,
            "list" => [
                [
                    "id" => $this->program->id,
                    "name" => $this->program->name,
                    "published" => $this->program->published,
                ],
                [
                    "id" => $this->programOne->id,
                    "name" => $this->programOne->name,
                    "published" => $this->programOne->published,
                ],
            ],
        ];
        $this->get($this->programUri, $this->manager->token)
            ->seeStatusCode(200)
            ->seeJsonContains($response);
    }
    public function test_showAll_userNotManager_error401()
    {
        $this->get($this->programUri, $this->removedManager->token)
            ->seeStatusCode(401);
    }
}
