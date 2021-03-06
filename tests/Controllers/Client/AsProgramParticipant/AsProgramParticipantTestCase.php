<?php

namespace Tests\Controllers\Client\AsProgramParticipant;

use Tests\Controllers\ {
    Client\ClientTestCase,
    RecordPreparation\Firm\Client\RecordOfClientParticipant,
    RecordPreparation\Firm\Program\RecordOfParticipant,
    RecordPreparation\Firm\RecordOfClient,
    RecordPreparation\Firm\RecordOfProgram,
    RecordPreparation\RecordOfFirm
};

class AsProgramParticipantTestCase extends ClientTestCase
{

    protected $asProgramParticipantUri;

    /**
     *
     * @var RecordOfClientParticipant
     */
    protected $programParticipation;

    /**
     *
     * @var RecordOfClientParticipant
     */
    protected $inactiveProgramParticipation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Firm')->truncate();
        $this->connection->table('Program')->truncate();
        $this->connection->table('Client')->truncate();
        $this->connection->table('Participant')->truncate();
        $this->connection->table('ClientParticipant')->truncate();

        $firm = new RecordOfFirm(999, 'firm_identifier');
        $this->connection->table('Firm')->insert($firm->toArrayForDbEntry());

        $program = new RecordOfProgram($firm, 999);
        $this->connection->table('Program')->insert($program->toArrayForDbEntry());

        $client = new RecordOfClient($firm, 999);
        $client->email = "purnama.adi+clientParticipant@gmail.com";
        $clientOne = new RecordOfClient($firm, 998);
        $client->activated = true;
        $clientOne->activated = true;
        $this->connection->table('Client')->insert($client->toArrayForDbEntry());
        $this->connection->table('Client')->insert($clientOne->toArrayForDbEntry());

        $participant = new RecordOfParticipant($program, 999);
        $inactiveParticipant = new RecordOfParticipant($program, 998);
        $inactiveParticipant->active = false;
        $this->connection->table('Participant')->insert($participant->toArrayForDbEntry());
        $this->connection->table('Participant')->insert($inactiveParticipant->toArrayForDbEntry());

        $this->programParticipation = new RecordOfClientParticipant($client, $participant);
        $this->inactiveProgramParticipation = new RecordOfClientParticipant($clientOne, $inactiveParticipant);
        $this->connection->table('ClientParticipant')->insert($this->programParticipation->toArrayForDbEntry());
        $this->connection->table('ClientParticipant')->insert($this->inactiveProgramParticipation->toArrayForDbEntry());

        $this->asProgramParticipantUri = $this->clientUri . "/as-program-participant/{$program->id}";
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Firm')->truncate();
        $this->connection->table('Program')->truncate();
        $this->connection->table('Client')->truncate();
        $this->connection->table('Participant')->truncate();
        $this->connection->table('ClientParticipant')->truncate();
    }
    
    protected function setInactiveParticipant()
    {
        $this->connection->table("Participant")->truncate();
        $this->programParticipation->participant->active = false;
        $this->connection->table("Participant")->insert($this->programParticipation->participant->toArrayForDbEntry());
    }

}
