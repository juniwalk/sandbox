<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity\Attributes;

use App\Entity\Parameter;
use Doctrine\ORM\Mapping as ORM;

trait Parametrized
{
	/**
	 * @ORM\OneToMany(targetEntity="Parameter", mappedBy="user", indexBy="key", cascade={"PERSIST"}, orphanRemoval=true)
	 * @ORM\OrderBy({"key" = "ASC"})
	 * @var Parameter[]
	 */
	private $params;


	/**
	 * @param  string  $key
	 * @return bool
	 */
	public function hasParam(string $key): bool
	{
		return isset($this->params[$key]);
	}


	/**
	 * @param  string  $key
	 * @param  mixed  $value
	 * @param  bool  $overwrite
	 * @return void
	 */
	public function setParam(string $key, $value, bool $overwrite = false): void
	{
		if ($overwrite && $this->hasParam($key)) {
			return;
		}

		if (is_null($value)) {
			unset($this->params[$key]);
			return;
		}

		$param = $this->params[$key] ?? new Parameter($key, $value, $this);
		$param->setValue($value);

		$this->params[$key] = $param;
	}


	/**
	 * @param  string  $key
	 * @return mixed
	 */
	public function getParam(string $key)
	{
		if (!$this->hasParam($key)) {
			return null;
		}

		return $this->params[$key]->getValue();
	}


	/**
	 * @param  bool  $unwrap
	 * @return mixed[]
	 */
	public function getParams(bool $unwrap = true): iterable
	{
		$params = [];

		if (!$unwrap) {
			return $this->params->toArray();
		}

		foreach ($this->params as $key => $param) {
			$params[$key] = $param->getValue();
		}

		return $params;
	}
}
