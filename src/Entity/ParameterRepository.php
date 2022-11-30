<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity;

use JuniWalk\Utils\ORM\AbstractRepository;

final class ParameterRepository extends AbstractRepository
{
	protected string $entityName = Parameter::class;


	public function findByUser(User $user, callable $where = null, ?int $maxResults = null): array
	{
		$where = function($qb) use ($user, $where) {
			if (is_callable($where)) {
				$qb = $where($qb) ?: $qb;
			}

			$qb->andWhere('e.user = :user')
				->setParameter(':user', $user)
				->orderBy('e.name', 'ASC');
		};

		return $this->findBy($where, $maxResults);
	}
}
