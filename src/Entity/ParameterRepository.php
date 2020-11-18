<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity;

use Doctrine\ORM\NoResultException;
use Nette\Application\BadRequestException;

final class ParameterRepository extends AbstractRepository
{
	/** @var string */
	protected $entityName = Parameter::class;


	/**
	 * @param  User  $user
	 * @return Parameter[]
	 */
	public function findByUser(User $user): iterable
	{
		$query = $this->createQueryBuilder('e')
			->where('e.user = :user');

		try {
			return $query->getQuery()
				->setParameter('user', $user)
				->getSingleResult();

		} catch (NoResultException $e) {
		}

		return null;
	}
}
