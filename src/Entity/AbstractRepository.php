<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity;

use App\Exceptions\EntityNotFoundException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NoResultException;
use Nette\Application\BadRequestException;

abstract class AbstractRepository
{
	/** @var EntityManager */
	protected $entityManager;

	/** @var Connection */
	protected $connection;

	/** @var string */
	protected $entityName;


	/**
	 * @param  EntityManager  $entityManager
	 * @throws EntityNotFoundException
	 */
	final public function __construct(EntityManager $entityManager)
	{
		$this->connection = $entityManager->getConnection();
		$this->entityManager = $entityManager;

		if (!$this->entityName) {
			throw EntityNotFoundException::fromName($this->entityName);
		}
	}


	/**
	 * @param  callable  $where
	 * @return object[]
	 */
	public function findBy(callable $where): iterable
	{
		$builder = $this->createQueryBuilder('e', 'e.id');
		$builder = $where($builder) ?: $builder;

		try {
			return $builder->getQuery()
				->getResult();

		} catch (NoResultException $e) {
			return [];
		}
	}


	/**
	 * @param  callable  $where
	 * @return object|null
	 */
	public function findOneBy(callable $where)
	{
		$builder = $this->createQueryBuilder('e', 'e.id');
		$builder = $where($builder) ?: $builder;

		try {
			return $builder->getQuery()
				->getSingleResult();

		} catch (NoResultException $e) {
			return null;
		}
	}


	/**
	 * @param  int  $id
	 * @return object|null
	 */
	public function findById(int $id)
	{
		return $this->findOneBy(function($qb) use ($id) {
			$qb->setParameter('id', $id);
			$qb->where('e.id = :id');
		});
	}


	/**
	 * @param  int  $id
	 * @return object
	 * @throws BadRequestException
	 */
	public function getById(int $id)
	{
		if (!$object = $this->findById($id)) {
			throw new BadRequestException;
		}

		return $object;
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
	 * @return object|null
	 */
	public function getReference(?int $id, string $entityName = null)
	{
		if (!$id || empty($id)) {
			return null;
		}

		return $this->entityManager->getReference($entityName ?: $this->entityName, $id);
	}


	/**
	 * @param  bool  $cascade
	 * @param  string|null  $entityName
	 * @return void
	 * @throws DBALException
	 */
	public function truncateTable(bool $cascade = false, string $entityName = null): void
	{
		$tableName = $this->getTableName($entityName);

		$this->query('TRUNCATE TABLE '.$tableName.' RESTART IDENTITY'.($cascade == true ? ' CASCADE' : null));
	}


	/**
	 * @param  string|null  $entityName
	 * @return string
	 */
	public function getTableName(string $entityName = null): string
	{
		$entityName = $entityName ?: $this->entityName;
		$metaData = $this->entityManager->getClassMetadata($entityName);
		$tableName = $metaData->getTableName();

		if ($schemaName = $metaData->getSchemaName()) {
			$tableName = $schemaName.'.'.$tableName;
		}

		return $tableName;
	}


	/**
	 * @param  string  $query
	 * @return mixed
	 * @throws DBALException
	 */
	private function query(string $query)
	{
		return $this->connection->query($query);
	}
}
