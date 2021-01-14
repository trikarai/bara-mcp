<?php

namespace Firm\Domain\Model\Firm;

use DateTimeImmutable;
use Firm\Domain\Model\AssetBelongsToFirm;
use Firm\Domain\Model\Firm;
use Firm\Domain\Model\Firm\Program\ActivityType;
use Firm\Domain\Model\Firm\Program\Consultant;
use Firm\Domain\Model\Firm\Program\ConsultationSetup;
use Firm\Domain\Model\Firm\Program\Coordinator;
use Firm\Domain\Model\Firm\Program\EvaluationPlan;
use Firm\Domain\Model\Firm\Program\EvaluationPlanData;
use Firm\Domain\Model\Firm\Program\MeetingType\CanAttendMeeting;
use Firm\Domain\Model\Firm\Program\MeetingType\Meeting;
use Firm\Domain\Model\Firm\Program\MeetingType\Meeting\Attendee;
use Firm\Domain\Model\Firm\Program\MeetingType\MeetingData;
use Firm\Domain\Model\Firm\Program\Mission;
use Firm\Domain\Model\Firm\Program\ProgramsProfileForm;
use Firm\Domain\Model\Shared\FormData;
use Firm\Domain\Service\ActivityTypeDataProvider;
use Resources\Domain\ValueObject\Password;
use Resources\Exception\RegularException;
use Resources\ValidationRule;
use Resources\ValidationService;
use SharedContext\Domain\ValueObject\ActivityParticipantType;

class Manager implements CanAttendMeeting
{

    /**
     *
     * @var Firm
     */
    protected $firm;

    /**
     *
     * @var string
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $email;

    /**
     *
     * @var Password
     */
    protected $password;

    /**
     *
     * @var string
     */
    protected $phone;

    /**
     *
     * @var DateTimeImmutable
     */
    protected $joinTime;

    /**
     *
     * @var bool
     */
    protected $removed = false;

    private function setName($name)
    {
        $errorDetail = 'bad request: manager name is required';
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($name, $errorDetail);
        $this->name = $name;
    }

    private function setEmail($email)
    {
        $errorDetail = 'bad request: manager email is required and must be in valid email format';
        ValidationService::build()
                ->addRule(ValidationRule::email())
                ->execute($email, $errorDetail);
        $this->email = $email;
    }

    private function setPhone($phone)
    {
        $errorDetail = 'bad request: manager phone must be in valid phone format';
        ValidationService::build()
                ->addRule(ValidationRule::optional(ValidationRule::phone()))
                ->execute($phone, $errorDetail);
        $this->phone = $phone;
    }

    function __construct(Firm $firm, string $id, ManagerData $managerData)
    {
        $this->firm = $firm;
        $this->id = $id;
        $this->setName($managerData->getName());
        $this->setEmail($managerData->getEmail());
        $this->password = new Password($managerData->getPassword());
        $this->setPhone($managerData->getPhone());
        $this->joinTime = new DateTimeImmutable();
        $this->removed = false;
    }

    public function createActivityTypeInProgram(
            Program $program, string $activityTypeId, ActivityTypeDataProvider $activityTypeDataProvider): ActivityType
    {
        if ($this->removed) {
            $errorDetail = "forbidden: only active manager can make this request";
            throw RegularException::forbidden($errorDetail);
        }
        if (!$program->belongsToFirm($this->firm)) {
            $errorDetail = "forbidden: can only manage asset of same firm";
            throw RegularException::forbidden($errorDetail);
        }
        return $program->createActivityType($activityTypeId, $activityTypeDataProvider);
    }

    public function updateActivityType(ActivityType $activityType, ActivityTypeDataProvider $activityTypeDataProvider): void
    {
        $this->assertAssetBelongsToSameFirm($activityType);
        $activityType->update($activityTypeDataProvider);
    }

    public function disableActivityType(ActivityType $activityType): void
    {
        $this->assertAssetBelongsToSameFirm($activityType);
        $activityType->disable();
    }

    public function enableActivityType(ActivityType $activityType): void
    {
        $this->assertAssetBelongsToSameFirm($activityType);
        $activityType->enable();
    }

    public function canInvolvedInProgram(Program $program): bool
    {
        return !$this->removed && $program->belongsToFirm($this->firm);
    }

    public function registerAsAttendeeCandidate(Attendee $attendee): void
    {
        $attendee->setManagerAsAttendeeCandidate($this);
    }

    public function roleCorrespondWith(ActivityParticipantType $role): bool
    {
        return $role->isManagerType();
    }

    public function initiateMeeting(string $meetingId, ActivityType $meetingType, MeetingData $meetingData): Meeting
    {
        if ($this->removed) {
            $errorDetail = "forbidden: only active manager can make this request";
            throw RegularException::forbidden($errorDetail);
        }
        if (!$meetingType->belongsToFirm($this->firm)) {
            $errorDetail = "forbidden: unable to manage meeting type from other firm";
            throw RegularException::forbidden($errorDetail);
        }
        return $meetingType->createMeeting($meetingId, $meetingData, $this);
    }

    public function disableCoordinator(Coordinator $coordinator): void
    {
        $this->assertAssetBelongsToSameFirm($coordinator);
        $coordinator->disable();
    }

    public function disableConsultant(Consultant $consultant): void
    {
        $this->assertAssetBelongsToSameFirm($consultant);
        $consultant->disable();
    }

    public function disablePersonnel(Personnel $personnel): void
    {
        $this->assertAssetBelongsToSameFirm($personnel);
        $personnel->disable();
    }
    
    public function enablePersonnel(Personnel $personnel): void
    {
        $this->assertAssetBelongsToSameFirm($personnel);
        $personnel->enable();
    }

    public function createEvaluationPlanInProgram(
            Program $program, string $evaluationPlanId, EvaluationPlanData $evaluationPlanData, FeedbackForm $reportForm): EvaluationPlan
    {
        $this->assertAssetBelongsToSameFirm($program);
        $this->assertAssetBelongsToSameFirm($reportForm);
        return $program->createEvaluationPlan($evaluationPlanId, $evaluationPlanData, $reportForm);
    }

    public function updateEvaluationPlan(
            EvaluationPlan $evaluationPlan, EvaluationPlanData $evaluationPlanData, FeedbackForm $reportForm): void
    {
        $this->assertAssetBelongsToSameFirm($evaluationPlan);
        $this->assertAssetBelongsToSameFirm($reportForm);
        $evaluationPlan->update($evaluationPlanData, $reportForm);
    }

    public function disableEvaluationPlan(EvaluationPlan $evaluationPlan): void
    {
        $this->assertAssetBelongsToSameFirm($evaluationPlan);
        $evaluationPlan->disable();
    }

    public function enableEvaluationPlan(EvaluationPlan $evaluationPlan): void
    {
        $this->assertAssetBelongsToSameFirm($evaluationPlan);
        $evaluationPlan->enable();
    }

    public function updateConsultationSetup(
            ConsultationSetup $consultationSetup, string $name, int $sessionDuration,
            FeedbackForm $participantFeedbackForm, FeedbackForm $consultantFeedbackForm): void
    {
        $this->assertAssetBelongsToSameFirm($consultationSetup);
        $this->assertAssetBelongsToSameFirm($participantFeedbackForm);
        $this->assertAssetBelongsToSameFirm($consultantFeedbackForm);
        $consultationSetup->update($name, $sessionDuration, $participantFeedbackForm, $consultantFeedbackForm);
    }
    
    public function createProfileForm(string $profileFormId, FormData $formData): ProfileForm
    {
        return new ProfileForm($this->firm, $profileFormId, $formData);
    }
    
    public function updateProfileForm(ProfileForm $profileForm, FormData $formData): void
    {
        $this->assertAssetBelongsToSameFirm($profileForm);
        $profileForm->update($formData);
    }
    
    public function assignProfileFormToProgram(Program $program, ProfileForm $profileForm): string
    {
        $this->assertAssetBelongsToSameFirm($program);
        $this->assertAssetBelongsToSameFirm($profileForm);
        return $program->assignProfileForm($profileForm);
    }
    
    public function disableProgramsProfileForm(ProgramsProfileForm $programsProfileForm): void
    {
        $this->assertAssetBelongsToSameFirm($programsProfileForm);
        $programsProfileForm->disable();
    }
    
    public function removeProgram(Program $program): void
    {
        $this->assertAssetBelongsToSameFirm($program);
        $program->remove();
    }
    
    public function changeMissionsWorksheetForm(Mission $mission, WorksheetForm $worksheetForm): void
    {
        $this->assertAssetBelongsToSameFirm($mission);
        $this->assertAssetBelongsToSameFirm($worksheetForm);
        $mission->changeWorksheetForm($worksheetForm);
    }

    protected function assertAssetBelongsToSameFirm(AssetBelongsToFirm $asset): void
    {
        if (!$asset->belongsToFirm($this->firm)) {
            $errorDetail = "forbidden: unable to manage asset from other firm";
            throw RegularException::forbidden($errorDetail);
        }
    }
    
    public function assignClientCVForm(ProfileForm $profileForm): string
    {
        return $this->firm->assignClientCVForm($profileForm);
    }
    
    public function disableClientCVForm(ClientCVForm $clientCVForm)
    {
        $this->assertAssetBelongsToSameFirm($clientCVForm);
        $clientCVForm->disable();
    }

}
