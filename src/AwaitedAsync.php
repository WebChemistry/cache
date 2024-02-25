<?php declare(strict_types = 1);

namespace WebChemistry\Cache;

use Override;

/**
 * @template T
 * @implements Async<T>
 */
final class AwaitedAsync implements Async
{

	/**
	 * @param T $value
	 */
	public function __construct(
		private mixed $value,
	)
	{
	}

	/**
	 * @return T
	 */
	#[Override]
	public function await(): mixed
	{
		return $this->value;
	}

}
