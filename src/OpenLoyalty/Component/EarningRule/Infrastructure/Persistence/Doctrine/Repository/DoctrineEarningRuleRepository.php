<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortByFilter;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortFilter;
use OpenLoyalty\Component\EarningRule\Domain\CustomEventEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleRepository;
use OpenLoyalty\Component\EarningRule\Domain\ReferralEarningRule;
use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\Functions\Cast;

/**
 * Class DoctrineEarningRuleRepository.
 */
class DoctrineEarningRuleRepository extends EntityRepository implements EarningRuleRepository
{
    use SortFilter, SortByFilter;

    /**
     * {@inheritdoc}
     */
    public function findAll($returnQueryBuilder = false)
    {
        if ($returnQueryBuilder) {
            return $this->createQueryBuilder('e');
        }

        return parent::findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function byId(EarningRuleId $earningRuleId)
    {
        return parent::find($earningRuleId);
    }

    /**
     * {@inheritdoc}
     */
    public function save(EarningRule $earningRule)
    {
        $this->getEntityManager()->persist($earningRule);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(EarningRule $earningRule)
    {
        $this->getEntityManager()->remove($earningRule);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = null, $direction = 'ASC', $returnQb = false)
    {
        $qb = $this->createQueryBuilder('e');

        if ($sortField) {
            $qb->orderBy(
                'e.'.$this->validateSort($sortField),
                $this->validateSortBy($direction)
            );
        }

        $qb->setMaxResults($perPage);
        $qb->setFirstResult(($page - 1) * $perPage);

        return $returnQb ? $qb : $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countTotal($returnQb = false)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('count(e.earningRuleId)');

        return $returnQb ? $qb : $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllActive(\DateTime $date = null)
    {
        if (!$date) {
            $date = new \DateTime();
        }

        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.active = :true')->setParameter('true', true);
        $qb->andWhere($qb->expr()->orX(
            'e.allTimeActive = :true',
            'e.startAt <= :date AND e.endAt >= :date'
        ))->setParameter('date', $date);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllActiveEventRules($eventName = null, array $segmentIds = [], $levelId = null, \DateTime $date = null)
    {
        $this->getEntityManager()->getConfiguration()->addCustomStringFunction('cast', Cast::class);

        if (!$date) {
            $date = new \DateTime();
        }

        $qb = $this->getEntityManager()->createQueryBuilder()->select('e');
        $qb->from('OpenLoyaltyEarningRule:EventEarningRule', 'e');
        $qb->andWhere('e.active = :true')->setParameter('true', true);
        $qb->andWhere($qb->expr()->orX(
            'e.allTimeActive = :true',
            'e.startAt <= :date AND e.endAt >= :date'
        ))->setParameter('date', $date);

        if ($eventName) {
            $qb->andWhere('e.eventName = :eventName')->setParameter('eventName', $eventName);
        }

        $levelOrSegment = $qb->expr()->orX();
        if ($levelId) {
            $levelId = ($levelId instanceof Identifier) ? $levelId->__toString() : $levelId;
            $levelOrSegment->add($qb->expr()->like('cast(e.levels as text)', ':levelId'));
            $qb->setParameter('levelId', '%'.$levelId.'%');
        }

        $i = 0;
        foreach ($segmentIds as $segmentId) {
            $segmentId = ($segmentId instanceof Identifier) ? $segmentId->__toString() : $segmentId;
            $levelOrSegment->add($qb->expr()->like('cast(e.segments as text)', ':segmentId'.$i));
            $qb->setParameter('segmentId'.$i, '%'.$segmentId.'%');
            ++$i;
        }

        $qb->andWhere($levelOrSegment);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByCustomEventName($eventName, array $segmentIds = [], $levelId = null, \DateTime $date = null)
    {
        $this->getEntityManager()->getConfiguration()->addCustomStringFunction('cast', Cast::class);

        if (!$date) {
            $date = new \DateTime();
        }

        $qb = $this->getEntityManager()->createQueryBuilder()->select('e');
        $qb->select('e')->from(CustomEventEarningRule::class, 'e');
        $qb->andWhere('e.active = :true')->setParameter('true', true);
        $qb->andWhere($qb->expr()->orX(
            'e.allTimeActive = :true',
            'e.startAt <= :date AND e.endAt >= :date'
        ))->setParameter('date', $date);
        $qb->andWhere('e.eventName = :eventName')->setParameter('eventName', $eventName);

        $levelOrSegment = $qb->expr()->orX();
        if ($levelId) {
            $levelId = ($levelId instanceof Identifier) ? $levelId->__toString() : $levelId;
            $levelOrSegment->add($qb->expr()->like('cast(e.levels as text)', ':levelId'));
            $qb->setParameter('levelId', '%'.$levelId.'%');
        }

        $i = 0;
        foreach ($segmentIds as $segmentId) {
            $segmentId = ($segmentId instanceof Identifier) ? $segmentId->__toString() : $segmentId;
            $levelOrSegment->add($qb->expr()->like('cast(e.segments as text)', ':segmentId'.$i));
            $qb->setParameter('segmentId'.$i, '%'.$segmentId.'%');
            ++$i;
        }

        $qb->andWhere($levelOrSegment);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findReferralByEventName($eventName, array $segmentIds = [], $levelId = null, \DateTime $date = null)
    {
        $this->getEntityManager()->getConfiguration()->addCustomStringFunction('cast', Cast::class);

        if (!$date) {
            $date = new \DateTime();
        }

        $qb = $this->getEntityManager()->createQueryBuilder()->select('e');
        $qb->select('e')->from(ReferralEarningRule::class, 'e');
        $qb->andWhere('e.active = :true')->setParameter('true', true);
        $qb->andWhere($qb->expr()->orX(
            'e.allTimeActive = :true',
            'e.startAt <= :date AND e.endAt >= :date'
        ))->setParameter('date', $date);
        $qb->andWhere('e.eventName = :eventName')->setParameter('eventName', $eventName);

        $levelOrSegment = $qb->expr()->orX();
        if ($levelId) {
            $levelId = ($levelId instanceof Identifier) ? $levelId->__toString() : $levelId;
            $levelOrSegment->add($qb->expr()->like('cast(e.levels as text)', ':levelId'));
            $qb->setParameter('levelId', '%'.$levelId.'%');
        }

        $i = 0;
        foreach ($segmentIds as $segmentId) {
            $segmentId = ($segmentId instanceof Identifier) ? $segmentId->__toString() : $segmentId;
            $levelOrSegment->add($qb->expr()->like('cast(e.segments as text)', ':segmentId'.$i));
            $qb->setParameter('segmentId'.$i, '%'.$segmentId.'%');
            ++$i;
        }

        $qb->andWhere($levelOrSegment);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function isCustomEventEarningRuleExist($eventName, $currentEarningRuleId = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()->select('count(e)')
            ->from(CustomEventEarningRule::class, 'e');
        if ($currentEarningRuleId) {
            $qb->andWhere('e.earningRuleId != :earning_rule_id')
                ->setParameter('earning_rule_id', $currentEarningRuleId);
        }
        $qb->andWhere('e.eventName = :event_name')->setParameter('event_name', $eventName);

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllActiveEventRulesBySegmentsAndLevels(\DateTime $date = null, array $segmentIds = [], $levelId = null)
    {
        $result = $this->getEarningRulesForLevelAndSegmentQueryBuilder($segmentIds, $levelId, $date);

        return $result->getQuery()->getResult();
    }

    /**
     * @param array          $segmentIds
     * @param null           $levelId
     * @param \DateTime|null $date
     *
     * @return \Doctrine\ORM\QueryBuilder
     *
     * @throws \Doctrine\ORM\ORMException
     */
    protected function getEarningRulesForLevelAndSegmentQueryBuilder(array $segmentIds = [], $levelId = null, \DateTime $date = null)
    {
        $this->getEntityManager()->getConfiguration()->addCustomStringFunction('cast', Cast::class);

        if (!$date) {
            $date = new \DateTime();
        }

        $qb = $this->createQueryBuilder('c');
        $qb->andWhere('c.active = :true')->setParameter('true', true);
        $qb->andWhere($qb->expr()->orX(
            'c.allTimeActive = :true',
            'c.startAt <= :date AND c.endAt >= :date'
        ))->setParameter('date', $date);

        $levelOrSegment = $qb->expr()->orX();
        if ($levelId) {
            $levelId = ($levelId instanceof Identifier) ? $levelId->__toString() : $levelId;
            $levelOrSegment->add($qb->expr()->like('cast(c.levels as text)', ':levelId'));
            $qb->setParameter('levelId', '%'.$levelId.'%');
        }

        $i = 0;
        foreach ($segmentIds as $segmentId) {
            $segmentId = ($segmentId instanceof Identifier) ? $segmentId->__toString() : $segmentId;
            $levelOrSegment->add($qb->expr()->like('cast(c.segments as text)', ':segmentId'.$i));
            $qb->setParameter('segmentId'.$i, '%'.$segmentId.'%');
            ++$i;
        }

        $qb->andWhere($levelOrSegment);

        return $qb;
    }
}
