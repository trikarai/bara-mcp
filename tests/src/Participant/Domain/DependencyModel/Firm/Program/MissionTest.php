<?php

namespace Participant\Domain\DependencyModel\Firm\Program;

use Doctrine\Common\Collections\ArrayCollection;
use Participant\Domain\DependencyModel\Firm\ {
    Program,
    WorksheetForm
};
use SharedContext\Domain\Model\SharedEntity\ {
    FormRecord,
    FormRecordData
};
use Tests\TestBase;

class MissionTest extends TestBase
{
    protected $mission;
    protected $worksheetForm;
    protected $branchMission;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->mission = new TestableMission();
        $this->mission->program = $this->buildMockOfClass(Program::class);
        $this->mission->branches = new ArrayCollection();
        $this->worksheetForm = $this->buildMockOfClass(WorksheetForm::class);
        $this->mission->worksheetForm = $this->worksheetForm;
        
        $this->branchMission = $this->buildMockOfClass(Mission::class);
    }
    
    public function test_programEquals_sameProgram_returnTrue()
    {
        $this->assertTrue($this->mission->programEquals($this->mission->program));
    }
    public function test_programEquals_differentProgram_returnFalse()
    {
        $program = $this->buildMockOfClass(Program::class);
        $this->assertFalse($this->mission->programEquals($program));
    }
    
    public function test_isRootMission_hasNoParent_returnTrue()
    {
        $this->assertTrue($this->mission->isRootMission());
    }
    public function test_isRootMission_hasParent_returnFalse()
    {
        $this->mission->parent = $this->buildMockOfClass(Mission::class);
        $this->assertFalse($this->mission->isRootMission());
    }
    protected function executeHasBranch()
    {
        return $this->mission->hasBranch($this->branchMission);
    }
    public function test_containBranch_missionInArgumentNotExistInBranch_returnFalse()
    {
        $this->assertFalse($this->executeHasBranch());
    }
    public function test_containBranch_missionInArgumentExistInBanch_returnTrue()
    {
        $this->mission->branches->add($this->branchMission);
        $this->assertTrue($this->executeHasBranch());
    }
    
    public function test_createWorksheetFormRecord_returnResultOfWorksheetFormsCreateFormRecord()
    {
        $this->worksheetForm->expects($this->once())
                ->method('createFormRecord')
                ->with($formRecordId = 'formRecordId', $formRecordData = $this->buildMockOfClass(FormRecordData::class))
                ->willReturn($formRecord = $this->buildMockOfClass(FormRecord::class));
        $this->assertEquals($formRecord, $this->mission->createWorksheetFormRecord($formRecordId, $formRecordData));
    }
    
}

class TestableMission extends Mission
{
    public $program, $parent, $id, $name, $description, $worksheetForm, $branches;
    
    public function __construct()
    {
        parent::__construct();
    }
}
