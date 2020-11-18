<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Security;

use App\Entity\User;
use App\Entity\UserRepository;
use App\Exceptions\EntityNotManagedException;
use Nette\Application\BadRequestException;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Ramsey\Uuid\Uuid;

final class AccessManager
{
	/** @var UserRepository */
	private $userRepository;

	/** @var Cache */
	private $cache;


	/**
     * @param UserRepository  $userRepository
	 * @param IStorage  $storage
	 */
	public function __construct(
		UserRepository $userRepository,
		IStorage $storage
	) {
		$this->cache = new Cache($storage, 'AccessManager.Tokens');
		$this->userRepository = $userRepository;
	}


	/**
	 * @param  string  $key
	 * @param  string[]  $data
	 * @param  string[]  $cache
	 * @return string
	 */
	public function createToken(string $key, iterable $data, iterable $cache = []): string
	{
		$token = (string) Uuid::uuid4();
		$data['key'] = $key;

		$this->cache->save($token, $data, $cache);
		return $token;
	}


	/**
	 * @param  User  $user
	 * @param  string|null  $slug
	 * @param  string[]  $cache
	 * @return string
	 * @throws EntityNotManagedException
	 */
	public function createSluggedToken(User $user, ?string $slug, iterable $cache = []): string
	{
		if (!$userId = (string) $user->getId()) {
			throw EntityNotManagedException::fromEntity($user);
		}

		return $this->createToken($userId, ['_slug' => $slug], $cache);
	}


	/**
	 * @param  string  $token
	 * @param  bool  $onLoadRemove
	 * @return string[]
	 * @throws BadRequestException
	 */
	public function validateToken(string $token, bool $onLoadRemove = true): iterable
	{
		if (!Uuid::isValid($token) || !$data = $this->cache->load($token)) {
			throw new BadRequestException('Access token has expired', 403);
		}

		if ($onLoadRemove) {
			$this->clearToken($token);
		}

		return $data;
	}


	/**
	 * @param  string  $slug
	 * @param  string  $token
	 * @param  bool  $onLoadRemove
	 * @return User
	 * @throws BadRequestException
	 */
	public function validateSluggedToken(string $slug, string $token, bool $onLoadRemove = true): User
	{
		$data = $this->validateToken($token, $onLoadRemove);

		if ($data['_slug'] && $data['_slug'] !== $slug) {
			throw new BadRequestException('Slug missmatch, expected '.$data['_slug'].' but '.$slug.' was given', 403);
		}

		unset($data['_slug']);
		return $this->userRepository->getReference((int) $data['key']);
	}


	/**
	 * @param string  $token
	 */
	public function clearToken(string $token): void
	{
		$this->cache->remove($token);
	}
}
