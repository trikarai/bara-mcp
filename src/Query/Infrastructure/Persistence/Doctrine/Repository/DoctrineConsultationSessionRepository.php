<?php

namespace Query\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\{
    EntityRepository,
    NoResultException,
    QueryBuilder
};
use Query\{
    Application\Service\Firm\Program\ConsultationSessionRepository,
    Domain\Model\Firm\Client\ClientParticipant,
    Domain\Model\Firm\Program\ConsultationSetup\ConsultationSession,
    Domain\Model\Firm\Team\TeamProgramParticipation,
    Domain\Model\User\UserParticipant,
    Infrastructure\QueryFilter\ConsultationSessionFilter
};
use Resources\{
    Exception\RegularException,
    Infrastructure\Persistence\Doctrine\PaginatorBuilder
};

class DoctrineConsultationSessionRepository extends EntityRepository implements ConsultationSessionRepository
{

    public function aConsultationSessionOfClient(
            string $firmId, string $clientId, string $programParticipationId, string $consultationSessionId): ConsultationSession
    {
        $params = [
            'firmId' => $firmId,
            'clientId' => $clientId,
            'programParticipationId' => $programParticipationId,
            'consultationSessionId' => $consultationSessionId,
        ];

        $clientParticipantQb = $this->getEntityManager()->createQueryBuilder();
        $clientParticipantQb->select('t_participant.id')
                ->from(ClientParticipant::class, 'clientParticipant')
                ->andWhere($clientParticipantQb->expr()->eq('clientParticipant.id', ':programParticipationId'))
                ->leftJoin('clientParticipant.client', 'client')
                ->leftJoin('clientParticipant.participant', 't_participant')
                ->andWhere($clientParticipantQb->expr()->eq('client.id', ':clientId'))
                ->leftJoin('client.firm', 'firm')
                ->andWhere($clientParticipantQb->expr()->eq('firm.id', ':firmId'))
                ->setMaxResults(1);

        $qb = $this->createQueryBuilder('consultationSession');
        $qb->select('consultationSession')
                ->andWhere($qb->expr()->eq('consultationSession.id', ':consultationSessionId'))
                ->leftJoin('consultationSession.participant', 'participant')
                ->andWhere($qb->expr()->in('participant.id', $clientParticipantQb->getDQL()))
                ->setParameters($params)
                ->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $ex) {
            $errorDetail = 'not found: consultation session not found';
            throw RegularException::notFound($errorDetail);
        }
    }

    public function aConsultationSessionOfPersonnel(
            string $firmId, string $personnelId, string $programConsultationId, string $consultationSessionId): ConsultationSession
    {
        $params = [
            'firmId' => $firmId,
            'personnelId' => $personnelId,
            'programConsultationId' => $programConsultationId,
            'consultationSessionId' => $consultationSessionId,
        ];
        $qb = $this->createQueryBuilder('consultationSession');
        $qb->select('consultationSession')
                ->andWhere($qb->expr()->eq('consultationSession.id', ':consultationSessionId'))
                ->leftJoin('consultationSession.consultant', 'programConsultation')
                ->andWhere($qb->expr()->eq('programConsultation.id', ':programConsultationId'))
                ->leftJoin('programConsultation.personnel', 'personnel')
                ->andWhere($qb->expr()->eq('personnel.id', ':personnelId'))
                ->leftJoin('personnel.firm', 'firm')
                ->andWhere($qb->expr()->eq('firm.id', ':firmId'))
                ->setParameters($params)
                ->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $ex) {
            $errorDetail = 'not found: consultation session not found';
            throw RegularException::notFound($errorDetail);
        }
    }

    public function allConsultationSessionsOfPersonnel(
            string $firmId, string $personnelId, string $programConsultationId, int $page, int $pageSize,
            ?ConsultationSessionFilter $consultationSessionFilter)
    {
        $params = [
            'firmId' => $firmId,
            'personnelId' => $personnelId,
            'programConsultationId' => $programConsultationId,
        ];
        $qb = $this->createQueryBuilder('consultationSession');
        $qb->select('consultationSession')
                ->leftJoin('consultationSession.consultant', 'programConsultation')
                ->andWhere($qb->expr()->eq('programConsultation.id', ':programConsultationId'))
                ->leftJoin('programConsultation.personnel', 'personnel')
                ->andWhere($qb->expr()->eq('personnel.id', ':personnelId'))
                ->leftJoin('personnel.firm', 'firm')
                ->andWhere($qb->expr()->eq('firm.id', ':firmId'))
                ->setParameters($params);

        $this->applyFilter($qb, $consultationSessionFilter);

        return PaginatorBuilder::build($qb->getQuery(), $page, $pageSize);
    }

    public function allConsultationsSessionOfClient(
            string $firmId, string $clientId, string $programParticipationId, int $page, int $pageSize,
            ConsultationSessionFilter $consultationSessionFilter)
    {
        $params = [
            'firmId' => $firmId,
            'clientId' => $clientId,
            'programParticipationId' => $programParticipationId,
        ];

        $clientParticipantQb = $this->getEntityManager()->createQueryBuilder();
        $clientParticipantQb->select('t_participant.id')
                ->from(ClientParticipant::class, 'clientParticipant')
                ->andWhere($clientParticipantQb->expr()->eq('clientParticipant.id', ':programParticipationId'))
                ->leftJoin('clientParticipant.client', 'client')
                ->leftJoin('clientParticipant.participant', 't_participant')
                ->andWhere($clientParticipantQb->expr()->eq('client.id', ':clientId'))
                ->leftJoin('client.firm', 'firm')
                ->andWhere($clientParticipantQb->expr()->eq('firm.id', ':firmId'))
                ->setMaxResults(1);

        $qb = $this->createQueryBuilder('consultationSession');
        $qb->select('consultationSession')
                ->leftJoin('consultationSession.participant', 'participant')
                ->andWhere($qb->expr()->in('participant.id', $clientParticipantQb->getDQL()))
                ->setParameters($params);

        $this->applyFilter($qb, $consultationSessionFilter);
        return PaginatorBuilder::build($qb->getQuery(), $page, $pageSize);
    }

    public function aConsultationSessionFromUserParticipant(string $userId, string $userParticipantId,
            string $consultationSessionId): ConsultationSession
    {
        $params = [
            'userId' => $userId,
            'userParticipantId' => $userParticipantId,
            'consultationSessionId' => $consultationSessionId,
        ];

        $userParticipantQb = $this->getEntityManager()->createQueryBuilder();
        $userParticipantQb->select('t_participant.id')
                ->from(UserParticipant::class, 'userParticipant')
                ->andWhere($userParticipantQb->expr()->eq('userParticipant.id', ':userParticipantId'))
                ->leftJoin('userParticipant.participant', 't_participant')
                ->leftJoin('userParticipant.user', 'user')
                ->andWhere($userParticipantQb->expr()->eq('user.id', ':userId'))
                ->setMaxResults(1);

        $qb = $this->createQueryBuilder('consultationSession');
        $qb->select('consultationSession')
                ->andWhere($qb->expr()->eq('consultationSession.id', ':consultationSessionId'))
                ->leftJoin('consultationSession.participant', 'participant')
                ->andWhere($qb->expr()->in('participant.id', $userParticipantQb->getDQL()))
                ->setParameters($params)
                ->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $ex) {
            $errorDetail = 'not found: consultation session not found';
            throw RegularException::notFound($errorDetail);
        }
    }

    public function allConsultationSessionFromUserParticipant(string $userId, string $userParticipantId, int $page,
            int $pageSize, ?ConsultationSessionFilter $consultationSessionFilter)
    {
        $params = [
            'userId' => $userId,
            'userParticipantId' => $userParticipantId,
        ];

        $userParticipantQb = $this->getEntityManager()->createQueryBuilder();
        $userParticipantQb->select('t_participant.id')
                ->from(UserParticipant::class, 'userParticipant')
                ->andWhere($userParticipantQb->expr()->eq('userParticipant.id', ':userParticipantId'))
                ->leftJoin('userParticipant.participant', 't_participant')
                ->leftJoin('userParticipant.user', 'user')
                ->andWhere($userParticipantQb->expr()->eq('user.id', ':userId'))
                ->setMaxResults(1);

        $qb = $this->createQueryBuilder('consultationSession');
        $qb->select('consultationSession')
                ->leftJoin('consultationSession.participant', 'participant')
                ->andWhere($qb->expr()->in('participant.id', $userParticipantQb->getDQL()))
                ->setParameters($params);

        $this->applyFilter($qb, $consultationSessionFilter);
        return PaginatorBuilder::build($qb->getQuery(), $page, $pageSize);
    }

    public function aConsultationSessionBelongsToTeam(string $teamId, string $consultationSessionId): ConsultationSession
    {
        $params = [
            "teamId" => $teamId,
            "consultationSessionId" => $consultationSessionId,
        ];

        $participantQb = $this->getEntityManager()->createQueryBuilder();
        $participantQb->select("programParticipation.id")
                ->from(TeamProgramParticipation::class, "teamProgramParticipation")
                ->leftJoin("teamProgramParticipation.programParticipation", "programParticipation")
                ->leftJoin("teamProgramParticipation.team", "team")
                ->andWhere($participantQb->expr()->eq("team.id", ":teamId"))
                ->setMaxResults(1);

        $qb = $this->createQueryBuilder("consultationSession");
        $qb->select("consultationSession")
                ->andWhere($qb->expr()->eq("consultationSession.id", ":consultationSessionId"))
                ->leftJoin("consultationSession.participant", "participant")
                ->andWhere($qb->expr()->in("participant.id", $participantQb->getDQL()))
                ->setParameters($params)
                ->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $ex) {
            $errorDetail = "not found: consultation session not found";
            throw RegularException::notFound($errorDetail);
        }
    }

    public function allConsultationSessionsInProgramParticipationOfTeam(string $teamId,
            string $teamProgramParticipationId, int $page, int $pageSize,
            ?ConsultationSessionFilter $consultationSessionFilter)
    {
        $params = [
            "teamId" => $teamId,
            "teamProgramParticipationId" => $teamProgramParticipationId,
        ];

        $participantQb = $this->getEntityManager()->createQueryBuilder();
        $participantQb->select("programParticipation.id")
                ->from(TeamProgramParticipation::class, "teamProgramParticipation")
                ->andWhere($participantQb->expr()->eq("teamProgramParticipation.id", ":teamProgramParticipationId"))
                ->leftJoin("teamProgramParticipation.programParticipation", "programParticipation")
                ->leftJoin("teamProgramParticipation.team", "team")
                ->andWhere($participantQb->expr()->eq("team.id", ":teamId"))
                ->setMaxResults(1);

        $qb = $this->createQueryBuilder("consultationSession");
        $qb->select("consultationSession")
                ->leftJoin("consultationSession.participant", "participant")
                ->andWhere($qb->expr()->in("participant.id", $participantQb->getDQL()))
                ->setParameters($params);

        $this->applyFilter($qb, $consultationSessionFilter);
        return PaginatorBuilder::build($qb->getQuery(), $page, $pageSize);
    }

    protected function applyFilter(QueryBuilder $qb, ?ConsultationSessionFilter $consultationSessionFilter): void
    {
        if (!isset($consultationSessionFilter)) {
            return;
        }
        if (!empty($consultationSessionFilter->getMinStartTime())) {
            $qb->andWhere($qb->expr()->gte("consultationSession.startEndTime.startDateTime", ":minStartTime"))
                    ->setParameter("minStartTime", $consultationSessionFilter->getMinStartTime());
        }
        if (!empty($consultationSessionFilter->getMaxEndTime())) {
            $qb->andWhere($qb->expr()->lte("consultationSession.startEndTime.endDateTime", ":maxEndTime"))
                    ->setParameter("maxEndTime", $consultationSessionFilter->getMaxEndTime());
        }
        if (!is_null($containConsultantFeedback = $consultationSessionFilter->isContainConsultantFeedback())) {
            if ($containConsultantFeedback) {
                $qb->leftJoin('consultationSession.consultantFeedback', 'consultantFeedback')
                        ->andWhere($qb->expr()->isNotNull('consultantFeedback.id'));
            } else {
                $qb->leftJoin('consultationSession.consultantFeedback', 'consultantFeedback')
                        ->andWhere($qb->expr()->isNull('consultantFeedback.id'));
            }
        }
        if (!is_null($containParticipantFeedback = $consultationSessionFilter->isContainParticipantFeedback())) {
            if ($containParticipantFeedback) {
                $qb->leftJoin('consultationSession.participantFeedback', 'participantFeedback')
                        ->andWhere($qb->expr()->isNotNull('participantFeedback.id'));
            } else {
                $qb->leftJoin('consultationSession.participantFeedback', 'participantFeedback')
                        ->andWhere($qb->expr()->isNull('participantFeedback.id'));
            }
        }
    }

    public function aConsultationSessionInProgram(string $programId, string $consultationSessionId): ConsultationSession
    {
        $params = [
            "programId" => $programId,
            "consultationSessionId" => $consultationSessionId,
        ];

        $qb = $this->createQueryBuilder("consultationSession");
        $qb->select("consultationSession")
                ->andWhere($qb->expr()->eq("consultationSession.id", ":consultationSessionId"))
                ->leftJoin("consultationSession.participant", "participant")
                ->leftJoin("participant.program", "program")
                ->andWhere($qb->expr()->eq("program.id", ":programId"))
                ->setParameters($params)
                ->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $ex) {
            $errorDetail = "not found: consultation session not found";
            throw RegularException::notFound($errorDetail);
        }
    }

    public function allConsultationSessionsInProgram(
            string $programId, int $page, int $pageSize, ?ConsultationSessionFilter $consultationSessionFilter)
    {
        $params = [
            "programId" => $programId,
        ];

        $qb = $this->createQueryBuilder("consultationSession");
        $qb->select("consultationSession")
                ->leftJoin("consultationSession.participant", "participant")
                ->leftJoin("participant.program", "program")
                ->andWhere($qb->expr()->eq("program.id", ":programId"))
                ->setParameters($params);

        $this->applyFilter($qb, $consultationSessionFilter);
        return PaginatorBuilder::build($qb->getQuery(), $page, $pageSize);
    }

}
