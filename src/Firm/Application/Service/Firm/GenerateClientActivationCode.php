<?php

namespace Firm\Application\Service\Firm;

use Resources\Application\Event\Dispatcher;

class GenerateClientActivationCode
{

    /**
     *
     * @var ClientRepository
     */
    protected $clientRepository;

    /**
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    public function __construct(ClientRepository $clientRepository, Dispatcher $dispatcher)
    {
        $this->clientRepository = $clientRepository;
        $this->dispatcher = $dispatcher;
    }

    public function execute(string $firmIdentifier, string $email): void
    {
        $client = $this->clientRepository->ofEmail($firmIdentifier, $email);
        $client->generateActivationCode();
        $this->clientRepository->update();
        
        $this->dispatcher->dispatch($client);
    }

}
