<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity;

use Doctrine\ORM\NoResultException;
use JuniWalk\Utils\ORM\AbstractRepository;

final class UserRepository extends AbstractRepository
{
	protected string $entityName = User::class;


	public function findByName(string $name, callable $where = null, ?int $maxResults = null): array
	{
		$name = Strings::toAscii($name);
		$where = function($qb) use ($name, $where) {
			if (is_callable($where)) {
				$qb = $where($qb) ?: $qb;
			}

			$qb->andWhere('LOWER(unaccent(e.name)) LIKE LOWER(:name)')
				->setParameter(':name', '%'.$name.'%')
				->orderBy('e.name', 'ASC');
		};

		return $this->findBy($where, $maxResults);
	}


	/**
	 * @throws NoResultException
	 */
	public function getByEmail(string $email): User
	{
		return $this->getOneBy(function($qb) use ($email) {
			$qb->where('LOWER(e.email) = LOWER(:email)')
				->setParameter(':email', $email);
		});
	}


	public function findByEmail(string $email): ?User
	{
		try {
			return $this->getByEmail($email);

		} catch (NoResultException) {
			return null;
		}
	}
}
