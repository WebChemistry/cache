<?php declare(strict_types = 1);

namespace WebChemistry\Cache;

/**
 * @template TDecorate
 * @template TValue
 * @implements Async<TValue>
 */
final class DecorateAsync implements Async
{

	/** @var callable(TDecorate $value): TValue */
	private $decorator;

	/**
	 * @param Async<TDecorate> $decorate
	 * @param callable(TDecorate $value): TValue $decorator
	 */
	public function __construct(
		private Async $decorate,
		callable $decorator,
	)
	{
		$this->decorator = $decorator;
	}

	public function await(): mixed
	{
		return ($this->decorator)($this->decorate->await());
	}

}
