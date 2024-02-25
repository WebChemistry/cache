<?php declare(strict_types = 1);

namespace WebChemistry\Cache;

use LogicException;
use Override;
use Redis;

final class RedisCache implements Cache
{

	private bool $inTransaction = false;

	/** @var array<callable(mixed): void> */
	private array $set = [];

	public function __construct(
		private Redis $redis,
		private ?string $namespace = null,
	)
	{
	}

	#[Override]
	public function namespace(string $namespace): Cache
	{
		return new self($this->redis, $namespace);
	}

	#[Override]
	public function keys(string $pattern): Async
	{
		/** @var Async<string[]> */
		return $this->callFn(
			fn () => $this->redis->keys($this->getKeyName($pattern)),
			fn (mixed $value): array => is_array($value) ? $value : [],
		);
	}

	#[Override]
	public function hGetAll(string $key): Async
	{
		/** @var Async<string[]> */
		return $this->callFn(
			fn () => $this->redis->hGetAll($this->getKeyName($key)),
			fn (mixed $value): array => is_array($value) ? $value : [],
		);
	}

	#[Override]
	public function hSet(string $key, array $values): Async
	{
		return $this->callFn(
			fn () => $this->redis->hMSet($this->getKeyName($key), $values),
			fn (mixed $value): bool => is_bool($value) ? $value : false,
		);
	}

	#[Override]
	public function hAppend(string $key, array $values): Async
	{
		return $this->callFn(
			function () use ($key, $values): mixed {
				$arguments = [$this->getKeyName($key)];

				foreach ($values as $k => $v) {
					$arguments[] = $k;
					$arguments[] = $v;
				}

				return $this->redis->eval(
					"if redis.call('exists', KEYS[1]) == 1 then return redis.call('hset', KEYS[1], unpack(ARGV)) end",
					$arguments,
					1,
				);
			},
			fn (mixed $value) => $value !== false,
		);
	}

	#[Override]
	public function set(string $key, float|bool|int|string|null $value): Async
	{
		return $this->callFn(
			fn () => $this->redis->set($this->getKeyName($key), $value),
			fn (mixed $value): bool => is_bool($value) ? $value : false,
		);
	}

	#[Override]
	public function get(string $key): Async
	{
		return $this->callFn(
			fn () => $this->redis->get($this->getKeyName($key)),
			fn (mixed $value): ?string => is_string($value) ? $value : null,
		);
	}

	#[Override]
	public function del(string ...$keys): Async
	{
		return $this->callFn(
			fn () => $this->redis->del(...array_map($this->getKeyName(...), $keys)),
			fn (mixed $value): bool => is_int($value) && $value > 0,
		);
	}

	#[Override]
	public function delMatch(string $pattern): Async
	{
		return $this->callFn(
			fn () => $this->redis->keys($pattern),
			function (mixed $value): bool {
				if (is_array($value) && $value) {
					$this->redis->del(...$value);

					return true;
				}

				return false;
			},
		);
	}

	#[Override]
	public function flushDatabase(): void
	{
		if ($this->inTransaction) {
			$this->commit();
		}

		$this->redis->flushDB();
	}

	private function getKeyName(string $key): string
	{
		return $this->namespace ? $this->namespace . ':' . $key : $key;
	}

	/**
	 * @param mixed[] $results
	 */
	private function processTransaction(array $results): void
	{
		foreach ($this->set as $i => $set) {
			if (!array_key_exists($i, $results)) {
				throw new LogicException('Transaction failed.');
			}

			$set($results[$i]);
		}

		$this->set = [];
	}

	/**
	 * @template T
	 * @template TRet
	 * @param callable(): TRet $fn
	 * @param callable(TRet $value): T $onSet
	 * @return Async<T>
	 */
	private function callFn(callable $fn, callable $onSet): Async
	{
		if (!$this->inTransaction) {
			$this->redis->multi();
		}

		$fn();

		[$value, $set] = AwaitAsync::create([$this, 'commit'], $onSet);

		$this->set[] = $set;

		return $value;
	}

	/**
	 * @internal
	 */
	public function commit(): void
	{
		$results = $this->redis->exec();

		if (!is_array($results)) {
			throw new LogicException('Transaction failed.');
		}

		$this->processTransaction($results);

		$this->inTransaction = false;
	}

}
