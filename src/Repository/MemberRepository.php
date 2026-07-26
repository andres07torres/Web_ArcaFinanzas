<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Member>
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function save(Member $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Member $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActiveMembers(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.status = :status')
            ->setParameter('status', 'active')
            ->orderBy('m.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getMemberCount(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getActiveMemberCount(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.status = :status')
            ->setParameter('status', 'active')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function searchMembers(string $query): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.firstName LIKE :query OR m.lastName LIKE :query OR m.email LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('m.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentMembers(int $limit = 5): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getMonthlyStats(): array
    {
        return $this->createQueryBuilder('m')
            ->select('MONTH(m.joinDate) as month, YEAR(m.joinDate) as year, COUNT(m.id) as count')
            ->groupBy('year, month')
            ->orderBy('year, month', 'DESC')
            ->setMaxResults(12)
            ->getQuery()
            ->getResult();
    }
}
