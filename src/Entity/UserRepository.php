<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Entity;

use Doctrine\ORM\NoResultException;
use Nette\Application\BadRequestException;

final class UserRepository extends AbstractRepository
{
	/** @var string */
	protected $entityName = User::class;


	/**
	 * @param  string  $email
	 * @return User
	 */
	public function getByEmail(string $email): User
	{
		$builder = $this->createQueryBuilder('e')
			->where('LOWER(e.email) = LOWER(:email)');

		try {
			return $builder->getQuery()
				->setParameter('email', $email)
				->getSingleResult();

		} catch (NoResultException $e) {
			throw new BadRequestException;
		}
	}


	/**
	 * @param  string  $email
	 * @return User|null
	 */
	public function findByEmail(string $email): ?User
	{
		try {
			return $this->getByEmail($email);

		} catch (BadRequestException $e) {
			return null;
		}
	}


	/**
	 * @param  callable|null  $where
	 * @param  int|null  $maxResults
	 * @return User[]
	 */
	public function findBy(?callable $where, ?int $maxResults = 5): iterable
	{
		$builder = $this->createQueryBuilder('e', 'e.id');

		if (is_callable($where)) {
			$builder = $where($builder) ?: $builder;
		}

		try {
			return $builder->getQuery()
				->setMaxResults($maxResults)
				->getResult();

		} catch (NoResultException $e) {
			return [];
		}
	}


	/**
	 * @param  string  $query
	 * @param  callable|null $where
	 * @param  int|null  $maxResults
	 * @return User[]
	 */
	public function findByName(string $query, callable $where = null, ?int $maxResults = 5): iterable
	{
		$builder = $this->createQueryBuilder('e', 'e.id')
			->where('LOWER(e.name) LIKE LOWER(:query)');

		if (is_callable($where)) {
			$builder = $where($builder) ?: $builder;
		}

		try {
			return $builder->getQuery()
				->setParameter('query', '%'.$query.'%')
				->setMaxResults($maxResults)
				->getResult();

		} catch (NoResultException $e) {
			return [];
		}
	}
}
