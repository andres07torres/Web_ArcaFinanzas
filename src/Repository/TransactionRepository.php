<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function save(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTotalByType(string $type): float
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as total')
            ->where('t.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();

        return (float) ($result['total'] ?? 0);
    }

    public function getBalance(): float
    {
        $totalIncome = $this->getTotalByType('income');
        $totalExpenses = $this->getTotalByType('expense');

        return $totalIncome - $totalExpenses;
    }

    public function findRecentTransactions(int $limit = 5): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.activity', 'a')
            ->addSelect('a')
            ->leftJoin('t.createdBy', 'u')
            ->addSelect('u')
            ->orderBy('t.transactionDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getTransactionCount(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTransactionsByCategory(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.category, SUM(t.amount) as total, COUNT(t.id) as count')
            ->groupBy('t.category')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getMonthlyTransactions(): array
    {
        return $this->createQueryBuilder('t')
            ->select('MONTH(t.transactionDate) as month, YEAR(t.transactionDate) as year, SUM(CASE WHEN t.type = :income THEN t.amount ELSE 0 END) as income, SUM(CASE WHEN t.type = :expense THEN t.amount ELSE 0 END) as expense')
            ->setParameter('income', 'income')
            ->setParameter('expense', 'expense')
            ->groupBy('year, month')
            ->orderBy('year, month', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }

    public function getTotalByTypeAndDateRange(
        string $type,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
        ?int $activityId = null,
        ?string $category = null
    ): float {
        $qb = $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as total')
            ->where('t.type = :type')
            ->setParameter('type', $type);

        if ($activityId) {
            $qb->leftJoin('t.activity', 'a');
        }

        $this->applyReportFilters($qb, $startDate, $endDate, $activityId, $category, null);

        $result = $qb->getQuery()->getOneOrNullResult();
        return (float) ($result['total'] ?? 0);
    }

    public function getReportTransactions(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
        ?int $activityId = null,
        ?string $category = null,
        ?string $type = null,
        int $page = 1,
        int $limit = 10
    ): array {
        $countQb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)');

        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.activity', 'a')
            ->addSelect('a')
            ->leftJoin('t.createdBy', 'u')
            ->addSelect('u');

        if ($activityId) {
            $countQb->leftJoin('t.activity', 'a');
        }

        $this->applyReportFilters($qb, $startDate, $endDate, $activityId, $category, $type);
        $this->applyReportFilters($countQb, $startDate, $endDate, $activityId, $category, $type);

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        $qb->orderBy('t.transactionDate', 'DESC')
           ->addOrderBy('t.id', 'DESC')
           ->setMaxResults($limit)
           ->setFirstResult(($page - 1) * $limit);

        $transactions = $qb->getQuery()->getResult();

        return [
            'transactions' => $transactions,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => $total > 0 ? (int) ceil($total / $limit) : 0,
        ];
    }

    private function applyReportFilters(
        \Doctrine\ORM\QueryBuilder $qb,
        ?\DateTimeInterface $startDate,
        ?\DateTimeInterface $endDate,
        ?int $activityId,
        ?string $category,
        ?string $type
    ): void {
        if ($startDate) {
            $qb->andWhere('t.transactionDate >= :startDate')
               ->setParameter('startDate', $startDate->format('Y-m-d'));
        }
        if ($endDate) {
            $qb->andWhere('t.transactionDate <= :endDate')
               ->setParameter('endDate', $endDate->format('Y-m-d'));
        }
        if ($activityId) {
            $qb->andWhere('a.id = :activityId')
               ->setParameter('activityId', $activityId);
        }
        if ($category) {
            $qb->andWhere('t.category = :category')
               ->setParameter('category', $category);
        }
        if ($type) {
            $qb->andWhere('t.type = :type')
               ->setParameter('type', $type);
        }
    }

    public function getTransactionCategories(): array
    {
        $result = $this->createQueryBuilder('t')
            ->select('DISTINCT t.category')
            ->where('t.category IS NOT NULL')
            ->orderBy('t.category', 'ASC')
            ->getQuery()
            ->getScalarResult();

        if (empty($result)) {
            return [];
        }

        if (is_string($result[0] ?? null)) {
            return $result;
        }

        return array_column($result, 'category');
    }

    public function searchTransactions(string $query, int $limit = 5): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.description LIKE :query OR t.category LIKE :query OR t.paymentMethod LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('t.transactionDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
