<?php

namespace Firm\Domain\Model\Firm;

use Doctrine\Common\Collections\ArrayCollection;
use Firm\Domain\Model\Firm;
use Firm\Domain\Model\Firm\Program\Consultant;
use Firm\Domain\Model\Firm\Program\Coordinator;
use Firm\Domain\Model\Firm\Program\Mission;
use Firm\Domain\Model\Firm\Program\Mission\MissionComment;
use Firm\Domain\Model\Firm\Program\Mission\MissionCommentData;
use Resources\Domain\ValueObject\PersonName;
use Tests\TestBase;

class PersonnelTest extends TestBase
{

    protected $personnel, $personName;
    protected $firm;
    protected $id = 'personnel-input', $firstName = 'hadi', $lastName = 'pranoto', $email = 'newPersonnel@email.org',
            $password = 'password123', $phone = '08231231231';
    protected $bio = "new bio";
    protected $programCoordinatorship;
    protected $programMentorship;
    protected $mission, $missionComment, $missionCommentId = 'missionCommentId', $missionCommentData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->firm = $this->buildMockOfClass(Firm::class);
        $personnelData = new PersonnelData('firstname', 'lastname', 'personnel@email.org', 'password123', '0812312312', "bio");
        $this->personnel = new TestablePersonnel($this->firm, 'id', $personnelData);
        
        $this->personnel->programCoordinatorships = new ArrayCollection();
        $this->personnel->programMentorships = new ArrayCollection();
        
        $this->programCoordinatorship = $this->buildMockOfClass(Coordinator::class);
        $this->personnel->programCoordinatorships->add($this->programCoordinatorship);
        
        $this->programMentorship = $this->buildMockOfClass(Consultant::class);
        $this->personnel->programMentorships->add($this->programMentorship);
        
        $this->mission = $this->buildMockOfClass(Mission::class);
        $this->missionComment = $this->buildMockOfClass(MissionComment::class);
        $this->missionCommentData = $this->buildMockOfClass(MissionCommentData::class);
    }
    
    protected function getPersonnelData()
    {
        return new PersonnelData(
                $this->firstName, $this->lastName, $this->email, $this->password, $this->phone, $this->bio);
    }

    protected function executeConstruct()
    {
        return new TestablePersonnel($this->firm, $this->id, $this->getPersonnelData());
    }
    public function test_construct_setProperties()
    {
        $personnel = $this->executeConstruct();
        $this->assertEquals($this->firm, $personnel->firm);
        $this->assertEquals($this->id, $personnel->id);

        $name = new PersonName($this->firstName, $this->lastName);
        $this->assertEquals($name, $personnel->name);

        $this->assertEquals($this->email, $personnel->email);
        $this->assertEquals($this->bio, $personnel->bio);
        $this->assertTrue($personnel->password->match($this->password));
        $this->assertEquals($this->YmdHisStringOfCurrentTime(), $personnel->joinTime->format('Y-m-d H:i:s'));
        $this->assertTrue($personnel->active);
    }
    public function test_construct_invalidEmail_throwEx()
    {
        $this->email = 'invalid format';
        $operation = function () {
            $this->executeConstruct();
        };
        $errorDetail = "bad request: personnel email is required in valid format";
        $this->assertRegularExceptionThrowed($operation, "Bad Request", $errorDetail);
    }
    public function test_constructi_invalidPhoneFormat_throwEx()
    {
        $this->phone = "invalid format";
        $operation = function () {
            $this->executeConstruct();
        };
        $errorDetail = "bad request: personnel phone format is invalid";
        $this->assertRegularExceptionThrowed($operation, "Bad Request", $errorDetail);
    }
    public function test_construct_emptyPhone_void()
    {
        $this->phone = null;
        $this->executeConstruct();
        $this->markAsSuccess();
    }

    public function test_getName_returnFullName()
    {
        $this->assertEquals($this->personnel->name->getFullName(), $this->personnel->getName());
    }
    
    protected function executeDisable()
    {
        $this->programCoordinatorship->expects($this->any())
                ->method("isActive")
                ->willReturn(false);
        $this->programMentorship->expects($this->any())
                ->method("isActive")
                ->willReturn(false);
        $this->programMentorship->expects($this->any())
                ->method("isActive")
                ->willReturn(false);
        $this->personnel->disable();
    }
    public function test_disable_setInactive()
    {
        $this->executeDisable();
        $this->assertFalse($this->personnel->active);
    }
    public function test_disable_hasActiveProgramCoordinatorship_forbiddenError()
    {
        $this->programCoordinatorship->expects($this->once())
                ->method("isActive")
                ->willReturn(true);
        $operation = function (){
            $this->executeDisable();
        };
        $errorDetail = "forbidden: unable to disable personnel still having active role as coordinator or mentor in program";
        $this->assertRegularExceptionThrowed($operation, "Forbidden", $errorDetail);
    }
    public function test_disable_hasActiveProgramMentorships_forbiddenError()
    {
        $this->programMentorship->expects($this->once())
                ->method("isActive")
                ->willReturn(true);
        $operation = function (){
            $this->executeDisable();
        };
        $errorDetail = "forbidden: unable to disable personnel still having active role as coordinator or mentor in program";
        $this->assertRegularExceptionThrowed($operation, "Forbidden", $errorDetail);
    }
    
    protected function executeEnable()
    {
        $this->personnel->enable();
    }
    public function test_enable_setActiveTrue()
    {
        $this->personnel->active = false;
        $this->executeEnable();
        $this->assertTrue($this->personnel->active);
    }
    public function test_enable_alreadyActive_forbidden()
    {
        $operation = function (){
            $this->executeEnable();
        };
        $errorDetail = "forbidden: personnel already active";
        $this->assertRegularExceptionThrowed($operation, "Forbidden", $errorDetail);
    }
    
    public function test_belongsToFirm_sameFirm_returnTrue()
    {
        $this->assertTrue($this->personnel->belongsToFirm($this->personnel->firm));
    }
    public function test_belongsToFirm_differentFirm_returnFalse()
    {
        $firm = $this->buildMockOfClass(Firm::class);
        $this->assertFalse($this->personnel->belongsToFirm($firm));
    }
    
    protected function executeSubmitCommentInMission()
    {
        $this->personnel->submitCommentInMission($this->mission, $this->missionCommentId, $this->missionCommentData);
    }
    public function test_submitCommentInMission_returnMissionsReceiveCommentResult()
    {
        $this->mission->expects($this->once())
                ->method('receiveComment')
                ->with($this->missionCommentId, $this->missionCommentData, $this->personnel->id, $this->personnel->name->getFullName());
        $this->executeSubmitCommentInMission();
    }
    
    protected function executeReplyMissionComment()
    {
        $this->personnel->replyMissionComment($this->missionComment, $this->missionCommentId, $this->missionCommentData);
    }
    public function test_replyMissionComment_returnMissionCommentssReceiveReplyResult()
    {
        $this->missionComment->expects($this->once())
                ->method('receiveReply')
                ->with($this->missionCommentId, $this->missionCommentData, $this->personnel->id, $this->personnel->name->getFullName());
        $this->executeReplyMissionComment();
    }

}

class TestablePersonnel extends Personnel
{

    public $firm, $id, $name, $email, $password, $phone, $joinTime, $active;
    public $bio;
    public $assignedAdmin;
    public $programCoordinatorships;
    public $programMentorships;

}
