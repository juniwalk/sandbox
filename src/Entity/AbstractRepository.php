<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity;

use App\Exceptions\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NoResultException;
use Nette\Application\BadRequestException;

abstract class AbstractRepository
{
	/** @var EntityManager */
	protected $entityManager;

	/** @var string */
	protected $entityName;


	/**
	 * @param  EntityManager  $entityManager
	 * @throws EntityNotFoundException
	 */
	final public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;

		if (!$this->entityName) {
			throw EntityNotFoundException::fromName($this->entityName);
		}
	}


	/**
	 * @param  int  $id
	 * @return object
	 * @throws BadRequestException
	 */
	public function getById(int $id)
	{
		$builder = $this->createQueryBuilder('e')
			->where('e.id = :id');

		try {
			return $builder->getQuery()
				->setParameter('id', $id)
				->getSingleResult();

		} catch (NoResultException $e) {
			throw new BadRequestException;
		}
	}


	/**
	 * @param  int  $id
	 * @return object|null
	 */
	public function findById(int $id)
	{
		try {
			return $this->getById($id);

		} catch (BadRequestException $e) {
			return null;
		}
	}


    /**
     * @param  string  $alias
     * @param  string|null  $indexBy
     * @return QueryBuilder
     */
    public function createQueryBuilder(string $alias, string $indexBy = null): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()->select($alias)
            ->from($this->entityName, $alias, $indexBy);
    }


    /**
     * @param  string|null  $dql
     * @return Query
     */
    public function createQuery(string $dql = null): Query
    {
        return $this->entityManager->createQuery($dql);
    }


	/**
	 * @param  int|null  $id
	 * @param  string|null  $entityName
	 * @return Proxy|Entity|null
	 */
	public function getReference(?int $id, string $entityName = null)
	{
		if (!$id || empty($id)) {
			return null;
		}

		return $this->entityManager->getReference($entityName ?: $this->entityName, $id);
	}
}
