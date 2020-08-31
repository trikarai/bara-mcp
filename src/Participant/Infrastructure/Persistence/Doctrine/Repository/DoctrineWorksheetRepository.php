<?php

namespace Participant\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\ {
    EntityRepository,
    NoResultException
};
use Participant\ {
    Application\Service\Participant\WorksheetRepository,
    Domain\Model\ClientParticipant,
    Domain\Model\Participant\Worksheet
};
use Resources\ {
    Exception\RegularException,
    Uuid
};

class DoctrineWorksheetRepository extends EntityRepository implements WorksheetRepository
{

    public function aWorksheetOfClientParticipant(string $firmId, string $clientId, string $programId,
            string $worksheetId): Worksheet
    {
        $params = [
            'firmId' => $firmId,
            'clientId' => $clientId,
            'programId' => $programId,
            'worksheetId' => $worksheetId,
        ];

        $clientParticipantQb = $this->getEntityManager()->createQueryBuilder();
        $clientParticipantQb->select('tParticipant.id')
                ->from(ClientParticipant::class, 'clientParticipant')
                ->leftJoin('clientParticipant.participant', 'tParticipant')
                ->leftJoin('clientParticipant.client', 'client')
                ->andWhere($clientParticipantQb->expr()->eq('client.id', ':clientId'))
                ->andWhere($clientParticipantQb->expr()->eq('client.firmId', ':firmId'))
                ->leftJoin('clientParticipant.program', 'program')
                ->andWhere($clientParticipantQb->expr()->eq('program.id', ':programId'))
                ->andWhere($clientParticipantQb->expr()->eq('program.firmId', ':firmId'))
                ->setMaxResults(1);

        $qb = $this->createQueryBuilder('worksheet');
        $qb->select('worksheet')
                ->andWhere($qb->expr()->eq('worksheet.id', ':worksheetId'))
                ->leftJoin('worksheet.participant', 'participant')
                ->andWhere($qb->expr()->in('participant.id', $clientParticipantQb->getDQL()))
                ->setParameters($params)
                ->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $ex) {
            $errorDetail = 'not found: worksheet not found';
            throw RegularException::notFound($errorDetail);
        }
    }

    public function aWorksheetOfUserParticipant(string $userId, string $firmId, string $programId, string $worksheetId): Worksheet
    {
        $params = [
            'userId' => $userId,
            'firmId' => $firmId,
            'programId' => $programId,
            'worksheetId' => $worksheetId,
        ];

        $userParticipantQb = $this->getEntityManager()->createQueryBuilder();
        $userParticipantQb->select('tParticipant.id')
                ->from(\Participant\Domain\Model\UserParticipant::class, 'userParticipant')
                ->leftJoin('userParticipant.participant', 'tParticipant')
                ->leftJoin('userParticipant.user', 'user')
                ->andWhere($userParticipantQb->expr()->eq('user.id', ':userId'))
                ->leftJoin('userParticipant.program', 'program')
                ->andWhere($userParticipantQb->expr()->eq('program.id', ':programId'))
                ->andWhere($userParticipantQb->expr()->eq('program.firmId', ':firmId'))
                ->setMaxResults(1);

        $qb = $this->createQueryBuilder('worksheet');
        $qb->select('worksheet')
                ->andWhere($qb->expr()->eq('worksheet.id', ':worksheetId'))
                ->leftJoin('worksheet.participant', 'participant')
                ->andWhere($qb->expr()->in('participant.id', $userParticipantQb->getDQL()))
                ->setParameters($params)
                ->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $ex) {
            $errorDetail = 'not found: worksheet not found';
            throw RegularException::notFound($errorDetail);
        }
    }

    public function add(Worksheet $worksheet): void
    {
        $em = $this->getEntityManager();
        $em->persist($worksheet);
        $em->flush();
    }

    public function nextIdentity(): string
    {
        return Uuid::generateUuid4();
    }

    public function update(): void
    {
        $this->getEntityManager()->flush();
    }

}
