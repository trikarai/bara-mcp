<?php

namespace Notification\Domain\Model\SharedEntity;

use Resources\Application\Service\MailMessageInterface;

class KonsultaMailMessage implements MailMessageInterface
{

    /**
     *
     * @var string
     */
    protected $subject;

    /**
     *
     * @var string
     */
    protected $greetings;

    /**
     *
     * @var string
     */
    protected $mainMessage;

    /**
     *
     * @var string
     */
    protected $url;

    /**
     *
     * @var string
     */
    protected $apiPath;

    public function __construct(string $subject, string $greetings, string $mainMessage, string $url, string $apiPath)
    {
        $this->subject = $subject;
        $this->greetings = $greetings;
        $this->mainMessage = $mainMessage;
        $this->url = $url;
        $this->apiPath = $apiPath;
    }

    public function appendRecipientFirstNameInGreetings(string $recipientFirstName): void
    {
        $this->greetings . ", $recipientFirstName";
    }

    public function prependApiPath(string $apiPath): void
    {
        $this->apiPath = $apiPath . "$this->apiPath";
    }

    public function getHtmlMessage(): string
    {
        return null;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getTextMessage(): string
    {
        $clickablePath = $this->url . $this->apiPath;
        return <<<_MESSAGE
{$this->greetings}

{$this->mainMessage}
$clickablePath;
_MESSAGE;
    }

}
