<?php

namespace Firm\Application\Service\Manager;

use Firm\{
    Application\Service\Personnel\ActivityTypeRepository,
    Domain\Model\Firm\Manager,
    Domain\Model\Firm\Program\ActivityType,
    Domain\Service\ActivityTypeDataProvider
};
use Tests\TestBase;

class UpdateActivityTypeTest extends TestBase
{

    protected $activityTypeRepository, $activityType;
    protected $managerRepository, $manager;
    protected $service;
    protected $firmId = "firmId", $managerId = "managerId", $activityTypeId = "activityTypeId", $activityTypeDataProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->activityType = $this->buildMockOfClass(ActivityType::class);
        $this->activityTypeRepository = $this->buildMockOfInterface(ActivityTypeRepository::class);
        $this->activityTypeRepository->expects($this->any())
                ->method("ofId")
                ->with($this->activityTypeId)
                ->willReturn($this->activityType);

        $this->manager = $this->buildMockOfClass(Manager::class);
        $this->managerRepository = $this->buildMockOfInterface(ManagerRepository::class);
        $this->managerRepository->expects($this->any())
                ->method("aManagerInFirm")
                ->with($this->firmId, $this->managerId)
                ->willReturn($this->manager);

        $this->service = new UpdateActivityType($this->activityTypeRepository, $this->managerRepository);

        $this->activityTypeDataProvider = $this->buildMockOfClass(ActivityTypeDataProvider::class);
    }
    
    protected function execute()
    {
        $this->service->execute($this->firmId, $this->managerId, $this->activityTypeId, $this->activityTypeDataProvider);
    }
    public function test_execute_managerUpdateActivityType()
    {
        $this->manager->expects($this->once())
                ->method("updateActivityType")
                ->with($this->activityType, $this->activityTypeDataProvider);
        $this->execute();
    }
    public function test_execute_updateRepository()
    {
        $this->activityTypeRepository->expects($this->once())
                ->method("update");
        $this->execute();
    }

}
