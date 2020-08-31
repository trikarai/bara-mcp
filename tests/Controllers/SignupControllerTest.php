<?php

namespace Tests\Controllers;

use DateTimeImmutable;
use Tests\Controllers\RecordPreparation\ {
    Firm\RecordOfClient,
    RecordOfFirm,
    RecordOfUser
};

class SignupControllerTest extends ControllerTestCase
{
    protected $firm;
    protected $client;
    protected $user;

    protected $clientSignupUri = "/api/client-signup";
    protected $clientSignupInput;
    
    protected $userSignupUri = '/api/user-signup';
    protected $userSignupInput = [
            "firstName" => 'adi',
            "lastName" => 'purnama',
            "email" => 'adi@barapraja.com',
            "password" => 'newPwd123',
    ];


    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Firm')->truncate();
        $this->connection->table('Client')->truncate();
        $this->connection->table('User')->truncate();
        
        $this->firm = new RecordOfFirm(0, 'firm_identifier');
        $this->connection->table('Firm')->insert($this->firm->toArrayForDbEntry());
        
        $this->client = new RecordOfClient($this->firm, 0);
        $this->connection->table('Client')->insert($this->client->toArrayForDbEntry());
        
        $this->user = new RecordOfUser(0);
        $this->connection->table('User')->insert($this->user->toArrayForDbEntry());
        
        $this->clientSignupInput = [
            "firmIdentifier" => $this->firm->identifier,
            "firstName" => 'adi',
            "lastName" => 'purnama',
            "email" => 'purnama.adi@gmail.com',
            "password" => 'newPwd123',
        ];
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Firm')->truncate();
        $this->connection->table('Client')->truncate();
        $this->connection->table('User')->truncate();
    }
    public function test_clientSignup()
    {
//use valid mail to check if activation mail sent
        $response = [
            "meta" => [
                "code" => 201,
                "type" => "Created",
            ],
        ];
        $this->post($this->clientSignupUri, $this->clientSignupInput)
            ->seeStatusCode(201)
            ->seeJsonContains($response);
        
        $clientRecord = [
            "Firm_id" => $this->firm->id,
            "firstName" => $this->clientSignupInput['firstName'],
            "lastName" => $this->clientSignupInput['lastName'],
            "email" => $this->clientSignupInput['email'],
            "activated" => false,
            'activationCodeExpiredTime' => (new \DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s'),
            'resetPasswordCode' => null,
            'resetPasswordCodeExpiredTime' => null,
        ];
        $this->seeInDatabase('Client', $clientRecord);
    }
    public function test_clientSignup_emailAlreadyRegistered_error409()
    {
        $this->clientSignupInput['email'] = $this->client->email;
        $this->post($this->clientSignupUri, $this->clientSignupInput)
            ->seeStatusCode(409);
    }
    
    public function test_userSignup()
    {
        $response = [
            "meta" => [
                "code" => 201,
                "type" => "Created",
            ],
        ];
        $this->post($this->userSignupUri, $this->userSignupInput)
            ->seeStatusCode(201)
            ->seeJsonContains($response);
        
        $userRecord = [
            'firstName' => $this->userSignupInput['firstName'],
            'lastName' => $this->userSignupInput['lastName'],
            'email' => $this->userSignupInput['email'],
            'activated' => false,
            'activationCodeExpiredTime' => (new DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s'),
            'resetPasswordCode' => null,
            'resetPasswordCodeExpiredTime' => null,
        ];
        $this->seeInDatabase("User", $userRecord);
    }
    public function test_userSignup_emailRegistered_409()
    {
        $this->userSignupInput['email'] = $this->user->email;
        $this->post($this->userSignupUri, $this->userSignupInput)
            ->seeStatusCode(409);
    }
}
 