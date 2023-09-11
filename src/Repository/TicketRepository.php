<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\DependencyInjection\Loader\Configurator\expr;

/**
 * @extends ServiceEntityRepository<Ticket>
 *
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;
    public function __construct(ManagerRegistry $registry ,EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        parent::__construct($registry, Ticket::class);
    }

    public function save(Ticket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ticket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Ticket[] Returns an array of Ticket objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }


    public function getLastInsert():array
    {
        $qb = $this->createQueryBuilder('t')->orderBy('t.date_creation', 'DESC')->setMaxResults(1);
        $query = $qb->getQuery();
        $dernierTickets = $query->execute();
        return $dernierTickets;
    }

    //Faire une fonction qui récupère les tickets par date limite décroissante
    public function getTicketsByDateLimite():array
    {
        $qb = $this->createQueryBuilder('t')->orderBy('t.date_limite', 'ASC');
        $query = $qb->getQuery();
        $ticketsByDateLimite = $query->execute();
        return $ticketsByDateLimite;
    }

    public function getTicketsByEtatTerminer():array{
        $etat = $this->em->getRepository(Etat::class)->findOneBy(["libelle" => "Terminé"]);

        $qb = $this->createQueryBuilder('t')->where('t.etat = :etat')->setParameter('etat',$etat);
        $query = $qb->getQuery();
        $ticketsByDateLimite = $query->execute();
        return $ticketsByDateLimite;
    }
    public function getTicketsWithoutEtat():array{
        $etat = $this->em->getRepository(Etat::class)->findOneBy(["libelle" => "Terminé"]);
        $qb = $this->createQueryBuilder('t')->where('t.etat != :etat')->setParameter('etat',$etat)->orderBy('t.date_limite', 'ASC');
        $query = $qb->getQuery();
        $ticketsByDateLimite = $query->execute();
        return $ticketsByDateLimite;
    }
}
