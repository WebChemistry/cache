<?php declare(strict_types = 1);

namespace WebChemistry\Cache;

/**
 * @template T
 * @implements Async<T>
 */
final class AwaitAsync implements Async
{

	/** @var (callable(): void)|null */
	private $trigger;

	/** @var (callable(mixed): T)|null */
	private $onSet;

	private mixed $value;

	/**
	 * @param callable(): void $trigger
	 * @param (callable(mixed): T)|null $onSet
	 */
	private function __construct(callable $trigger, ?callable $onSet = null)
	{
		$this->trigger = $trigger;
		$this->onSet = $onSet;
	}

	/**
	 * @template TReceive
	 * @template TSetValue
	 * @param callable(): void $trigger
	 * @param (callable(TReceive): TSetValue)|null $onSet
	 * @return array{self<TSetValue>, callable(mixed): void}
	 */
	public static function create(callable $trigger, ?callable $onSet = null): array
	{
		$value = new self($trigger, $onSet);

		$set = function (mixed $val) use ($value) {
			$value->setValue($val);
		};

		return [$value, $set];
	}

	/**
	 * @param Async<mixed>[] $results
	 * @return list<mixed>
	 */
	public static function awaitAll(array $results): array
	{
		$values = [];

		foreach ($results as $result) {
			$values[] = $result->await();
		}

		return $values;
	}

	private function setValue(mixed $value): void
	{
		$this->trigger = null;

		if ($onSet = $this->onSet) {
			$this->value = $onSet($value);

			$this->onSet = null;
		} else {
			$this->value = $value;
		}
	}

	/**
	 * @return T
	 */
	public function await(): mixed
	{
		if ($this->trigger) {
			($this->trigger)();
		}

		return $this->value;
	}

}
