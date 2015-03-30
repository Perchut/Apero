<?php

namespace Apero\EventBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * EventUserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EventUserRepository extends EntityRepository
{
	public function findbyEventandUser($eventID, $userID)
	{
		$qb = $this
			->createQueryBuilder('eu')
		;

		$qb->where('eu.user = :user')
				->setParameter('user', $userID)
			->andWhere('eu.event = :event')
				->setParameter('event', $eventID)
		;

		return $qb
			->getQuery()
			->getResult()
		;
	}
}
