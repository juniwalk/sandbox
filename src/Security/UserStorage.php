<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Nette\Http\Session;
use Nette\Http\UserStorage as Storage;
use Nette\Security\IIdentity;
use Nette\Security\Identity;

final class UserStorage extends Storage
{
	/** @var EntityManager */
	private $entityManager;


	/**
	 * @param Session  $session
	 * @param EntityManager  $entityManager
	 */
	public function __construct(
		Session $session,
		EntityManager $entityManager
	) {
		$this->entityManager = $entityManager;
		parent::__construct($session);
	}


	/**
	 * @param  IIdentity|null  $identity
	 * @return static
	 */
	public function setIdentity(?IIdentity $identity): self
	{
		if ($identity !== null) {
			$class = get_class($identity);

			if ($this->entityManager->getMetadataFactory()->hasMetadataFor($class)) {
				$cm = $this->entityManager->getClassMetadata($class);
				$identifier = $cm->getIdentifierValues($identity);
				$identity = new Identity($identifier, [], [
					'class' => $class,
				]);
			}
		}

		return parent::setIdentity($identity);
	}


	/**
	 * @return IIdentity|null
	 */
	public function getIdentity(): ?IIdentity
	{
		$identity = parent::getIdentity();

		if ($identity instanceof Identity) {
			return $this->entityManager->getReference($identity->class, $identity->getId());
		}

		return $identity;
	}
}
