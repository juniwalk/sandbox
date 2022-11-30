<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2021
 * @license   MIT License
 */

namespace App\Managers;

use App\Entity\User;
use App\Entity\UserRepository;
use App\Exceptions\EntityNotManagedException;
use Nette\Application\BadRequestException;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Ramsey\Uuid\Uuid;

final class AccessManager
{
	private Cache $cache;


	public function __construct(
		private UserRepository $userRepository,
		IStorage $storage
	) {
		$this->cache = new Cache($storage, 'AccessManager.Tokens');
	}


	public function createToken(string $key, array $data, array $cache = []): string
	{
		$token = (string) Uuid::uuid4();
		$data['key'] = $key;

		$this->cache->save($token, $data, $cache);
		return $token;
	}


	/**
	 * @throws EntityNotManagedException
	 */
	public function createSluggedToken(User $user, ?string $slug, array $cache = []): string
	{
		if (!$userId = (string) $user->getId()) {
			throw EntityNotManagedException::fromEntity($user);
		}

		return $this->createToken($userId, ['_slug' => $slug], $cache);
	}


	/**
	 * @throws BadRequestException
	 */
	public function validateToken(string $token, bool $onLoadRemove = true): array
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


	public function clearToken(string $token): void
	{
		$this->cache->remove($token);
	}
}
