<?php declare(strict_types = 1);

namespace WebChemistry\Cache;

use Override;

final class VoidCache implements Cache
{

	#[Override]
	public function namespace(string $namespace): Cache
	{
		return $this;
	}

	#[Override]
	public function isOk(): bool
	{
		return false;
	}

	#[Override]
	public function keys(string $pattern): Async
	{
		/** @var Async<string[]> */
		return new AwaitedAsync([]);
	}

	#[Override]
	public function hGetAll(string $key): Async
	{
		/** @var Async<string[]> */
		return new AwaitedAsync([]);
	}

	#[Override]
	public function hSet(string $key, array $values): Async
	{
		return new AwaitedAsync(true);
	}

	#[Override]
	public function hAppend(string $key, array $values): Async
	{
		return new AwaitedAsync(true);
	}

	#[Override]
	public function set(string $key, float|bool|int|string|null $value): Async
	{
		return new AwaitedAsync(true);
	}

	#[Override]
	public function get(string $key): Async
	{
		/** @var Async<string|null> */
		return new AwaitedAsync(null);
	}

	#[Override]
	public function del(string $key): Async
	{
		return new AwaitedAsync(true);
	}

	#[Override]
	public function delMatch(string $pattern): Async
	{
		return new AwaitedAsync(true);
	}

	#[Override]
	public function flushDatabase(): void
	{
	}

	public function commit(): bool
	{
		return true;
	}

}
