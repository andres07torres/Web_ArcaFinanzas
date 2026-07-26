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
}
