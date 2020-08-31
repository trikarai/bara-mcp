<?php

namespace Query\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\ {
    EntityRepository,
    NoResultException,
    QueryBuilder
};
use Query\ {
    Application\Service\Firm\Program\ConsulationSetup\ConsultationRequestFilter,
    Application\Service\Firm\Program\ConsulationSetup\ConsultationRequestRepository,
    Domain\Model\Firm\Program\ClientParticipant,
    Domain\Model\Firm\Program\ConsultationSetup\ConsultationRequest
};
use Resources\ {
    Exception\RegularException,
    Infrastructure\Persistence\Doctrine\PaginatorBuilder
};

class DoctrineConsultationRequestRepository extends EntityRepository implements ConsultationRequestRepository
{

    public function aConsultationRequestOfClient(string $firmId, string $clientId, string $programId,
            string $consultationRequestId): ConsultationRequest
    {
        $params = [
            'firmId' => $firmId,
            'clientId' => $clientId,
            'programId' => $programId,
            'consultationRequestId' => $consultationRequestId,
        ];

        $clientParticipantQb = $this->getEntityManager()->createQueryBuilder();
        $clientParticipantQb->select('tParticipant.id')
                ->from(ClientParticipant::class, 'clientParticipant')
                ->leftJoin('clientParticipant.participant', 'tParticipant')
                ->leftJoin('clientParticipant.client', 'client')
                ->andWhere($clientParticipantQb->expr()->eq('client.id', ':clientId'))
                ->leftJoin('clientParticipant.program', 'program')
                ->andWhere($clientParticipantQb->expr()->eq('program.id', ':programId'))
                ->leftJoin('client.firm', 'cFirm')
                ->leftJoin('program.firm', 'pFirm')
                ->andWhere($clientParticipantQb->expr()->eq('cFirm.id', ':firmId'))
                ->andWhere($clientParticipantQb->expr()->eq('pFirm.id', ':firmId'))
                ->setMaxResults(1);

        $qb = $this->createQueryBuilder('consultationRequest');
        $qb->select('consultationRequest')
                ->andWhere($qb->expr()->eq('consultationRequest.id', ':consultationRequestId'))
                ->leftJoin('consultationRequest.participant', 'participant')
                ->andWhere($qb->expr()->in('participant.id', $clientParticipantQb->getDQL()))
                ->setParameters($params)
                ->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $ex) {
            $errorDetail = 'not found: consultation request not found';
            throw RegularException::notFound($errorDetail);
        }
    }

    public function aConsultationRequestOfPersonnel(string $firmId, string $personnelId, string $programId,
            string $consultationRequestId): ConsultationRequest
    {
        $params = [
            'firmId' => $firmId,
            'personnelId' => $firmId,
            'programId' => $programId,
            'consultationRequestId' => $consultationRequestId,
        ];
        $qb = $this->createQueryBuilder('consulationRequest');
        $qb->select('consulationRequest')
                ->andWhere($qb->expr()->eq('consulationRequest.id', ':consulationRequestId'))
                ->leftJoin('consulationRequest.consultant', 'consultant')
                ->leftJoin('consultant.program', 'program')
                ->andWhere($qb->expr()->eq('program.id', ':programId'))
                ->leftJoin('consultant.personnel', 'personnel')
                ->andWhere($qb->expr()->eq('personnel.id', ':personnelId'))
                ->leftJoin('personnel.firm', 'firm')
                ->andWhere($qb->expr()->eq('firm.id', ':firmId'))
                ->setParameters($params)
                ->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $ex) {
            $errorDetail = 'not found: consultation request not found';
            throw RegularException::notFound($errorDetail);
        }
    }

    public function all(string $firmId, string $programId, string $consultationSetupId, int $page, int $pageSize,
            ?ConsultationRequestFilter $consultationRequestFilter)
    {
        $params = [
            'firmId' => $firmId,
            'programId' => $programId,
            'consultationSetupId' => $consultationSetupId,
        ];
        $qb = $this->createQueryBuilder('consulationRequest');
        $qb->select('consulationRequest')
                ->leftJoin('consulationRequest.consultationSetup', 'consultationSetup')
                ->andWhere($qb->expr()->eq('consultationSetup.id', ':consultationSetupId'))
                ->leftJoin('consultationSetup.program', 'program')
                ->andWhere($qb->expr()->eq('program.id', ':programId'))
                ->leftJoin('program.firm', 'firm')
                ->andWhere($qb->expr()->eq('firm.id', ':firmId'))
                ->setParameters($params);

        $this->applyFilter($qb, $consultationRequestFilter);

        return PaginatorBuilder::build($qb->getQuery(), $page, $pageSize);
    }

    public function allConsultationRequestsOfClient(
            string $firmId, string $clientId, string $programId, int $page, int $pageSize,
            ?ConsultationRequestFilter $consultationRequestFilter)
    {
        $params = [
            'firmId' => $firmId,
            'clientId' => $clientId,
            'programId' => $programId,
        ];

        $clientParticipantQb = $this->getEntityManager()->createQueryBuilder();
        $clientParticipantQb->select('tParticipant.id')
                ->from(ClientParticipant::class, 'clientParticipant')
                ->leftJoin('clientParticipant.participant', 'tParticipant')
                ->leftJoin('clientParticipant.client', 'client')
                ->andWhere($clientParticipantQb->expr()->eq('client.id', ':clientId'))
                ->leftJoin('clientParticipant.program', 'program')
                ->andWhere($clientParticipantQb->expr()->eq('program.id', ':programId'))
                ->leftJoin('client.firm', 'cFirm')
                ->leftJoin('program.firm', 'pFirm')
                ->andWhere($clientParticipantQb->expr()->eq('cFirm.id', ':firmId'))
                ->andWhere($clientParticipantQb->expr()->eq('pFirm.id', ':firmId'))
                ->setMaxResults(1);

        $qb = $this->createQueryBuilder('consulationRequest');
        $qb->select('consulationRequest')
                ->leftJoin('consulationRequest.participant', 'participant')
                ->andWhere($qb->expr()->in('participant.id', $clientParticipantQb->getDQL()))
                ->setParameters($params);

        $this->applyFilter($qb, $consultationRequestFilter);

        return PaginatorBuilder::build($qb->getQuery(), $page, $pageSize);
    }

    public function allConsultationRequestsOfPersonnel(
            string $firmId, string $personnelId, string $programId, int $page, int $pageSize,
            ?ConsultationRequestFilter $consultationRequestFilter)
    {
        $params = [
            'firmId' => $firmId,
            'personnelId' => $firmId,
            'programId' => $programId,
        ];
        $qb = $this->createQueryBuilder('consulationRequest');
        $qb->select('consulationRequest')
                ->leftJoin('consulationRequest.consultant', 'consultant')
                ->leftJoin('consultant.program', 'program')
                ->andWhere($qb->expr()->eq('program.id', ':programId'))
                ->leftJoin('consultant.personnel', 'personnel')
                ->andWhere($qb->expr()->eq('personnel.id', ':personnelId'))
                ->leftJoin('personnel.firm', 'firm')
                ->andWhere($qb->expr()->eq('firm.id', ':firmId'))
                ->setParameters($params);

        $this->applyFilter($qb, $consultationRequestFilter);

        return PaginatorBuilder::build($qb->getQuery(), $page, $pageSize);
    }

    public function ofId(string $firmId, string $programId, string $consultationSetupId, string $consultationRequestId): ConsultationRequest
    {
        $params = [
            'firmId' => $firmId,
            'programId' => $programId,
            'consultationSetupId' => $consultationSetupId,
            'consultationRequestId' => $consultationRequestId,
        ];
        $qb = $this->createQueryBuilder('consulationRequest');
        $qb->select('consulationRequest')
                ->andWhere($qb->expr()->eq('consulationRequest.id', ':consulationRequestId'))
                ->leftJoin('consulationRequest.consultationSetup', 'consultationSetup')
                ->andWhere($qb->expr()->eq('consultationSetup.id', ':consultationSetupId'))
                ->leftJoin('consultationSetup.program', 'program')
                ->andWhere($qb->expr()->eq('program.id', ':programId'))
                ->leftJoin('program.firm', 'firm')
                ->andWhere($qb->expr()->eq('firm.id', ':firmId'))
                ->setParameters($params)
                ->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $ex) {
            $errorDetail = 'not found: consultation request not found';
            throw RegularException::notFound($errorDetail);
        }
    }

    protected function applyFilter(QueryBuilder $qb, ?ConsultationRequestFilter $consultationRequestFilter): void
    {
        if (empty($consultationRequestFilter)) {
            return;
        }

        if (!empty($minStartTime = $consultationRequestFilter->getMinStartTime())) {
            $qb->andWhere($qb->expr()->gte('consultationRequest.startEndTime.startDateTime', ':minStartTime'))
                    ->setParameter('minStartTime', $minStartTime);
        }
        if (!empty($maxStartTime = $consultationRequestFilter->getMaxStartTime())) {
            $qb->andWhere($qb->expr()->lt('consultationRequest.startEndTime.startDateTime', ':maxStartTime'))
                    ->setParameter('maxStartTime', $maxStartTime);
        }
    }

}
