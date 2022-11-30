<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity\Traits;

use App\Entity\Parameter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait Parametrized
{
	#[ORM\OneToMany(targetEntity: Parameter::class, mappedBy: 'user', indexBy: 'key', cascade: ['persist'], orphanRemoval: true)]
	#[ORM\OrderBy(['key' => 'ASC'])]
	private Collection $params;


	public function hasParam(string $key): bool
	{
		return isset($this->params[$key]);
	}


	public function setParam(string $key, mixed $value, bool $overwrite = true): void
	{
		if (!$overwrite && $this->hasParam($key)) {
			return;
		}

		$param = $this->params[$key] ?? new Parameter($key, $value, $this);
		$param->setValue($value);

		$this->params[$key] = $param;

		if (is_null($param->getValue())) {
			unset($this->params[$key]);
		}
	}


	public function getParam(string $key): mixed
	{
		if (!$this->hasParam($key)) {
			return null;
		}

		return $this->params[$key]->getValue();
	}


	public function getParamRaw(string $key): ?Parameter
	{
		return $this->params[$key] ?? null;
	}


	public function getParamsRaw(): array
	{
		return $this->params->toArray();
	}


	public function getParams(): array
	{
		$params = [];

		foreach ($this->params as $key => $param) {
			$params[$key] = $param->getValue();
		}

		return $params;
	}
}
