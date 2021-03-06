<?php

namespace Notification\Domain\SharedModel\Notification;

use DateTimeImmutable;
use Notification\Domain\ {
    Model\Firm\Client,
    SharedModel\Notification
};
use Resources\DateTimeImmutableBuilder;

class ClientNotificationRecipient
{

    /**
     *
     * @var Notification
     */
    protected $notification;

    /**
     *
     * @var string
     */
    protected $id;

    /**
     *
     * @var Client
     */
    protected $client;

    /**
     *
     * @var bool
     */
    protected $read;
    
    /**
     *
     * @var DateTimeImmutable
     */
    protected $notifiedTime;

    public function __construct(Notification $notification, string $id, Client $client)
    {
        $this->notification = $notification;
        $this->id = $id;
        $this->client = $client;
        $this->read = false;
        $this->notifiedTime = DateTimeImmutableBuilder::buildYmdHisAccuracy();
    }


}
