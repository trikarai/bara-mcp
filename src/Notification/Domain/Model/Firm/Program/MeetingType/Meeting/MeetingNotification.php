<?php

namespace Notification\Domain\Model\Firm\Program\MeetingType\Meeting;

use Notification\Domain\Model\Firm\Client;
use Notification\Domain\Model\Firm\Manager;
use Notification\Domain\Model\Firm\Personnel;
use Notification\Domain\Model\Firm\Program\MeetingType\Meeting;
use Notification\Domain\Model\User;
use Notification\Domain\SharedModel\ContainNotification;
use Notification\Domain\SharedModel\ContainNotificationForAllUser;
use Notification\Domain\SharedModel\Notification;

class MeetingNotification implements ContainNotificationForAllUser
{

    /**
     * 
     * @var Meeting
     */
    protected $meeting;

    /**
     * 
     * @var string
     */
    protected $id;

    /**
     * 
     * @var Notification
     */
    protected $notification;

    function __construct(Meeting $meeting, string $id, string $message)
    {
        $this->meeting = $meeting;
        $this->id = $id;
        $this->notification = new Notification($id, $message);
    }

    public function addClientRecipient(Client $client): void
    {
        $this->notification->addClientRecipient($client);
    }

    public function addManagerRecipient(Manager $manager): void
    {
        $this->notification->addManagerRecipient($manager);
    }

    public function addPersonnelRecipient(Personnel $personnel): void
    {
        $this->notification->addPersonnelRecipient($personnel);
    }

    public function addUserRecipient(User $user): void
    {
        $this->notification->addUserRecipient($user);
    }

}
