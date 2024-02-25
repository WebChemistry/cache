<?php declare(strict_types = 1);

namespace WebChemistry\Cache;

interface Cache
{

	public function namespace(string $namespace): Cache;

	public function isOk(): bool;

	/**
	 * @return Async<string[]>
	 */
	public function keys(string $pattern): Async;

	/**
	 * @return Async<string[]>
	 */
	public function hGetAll(string $key): Async;

	/**
	 * @param array<scalar|null> $values
	 * @return Async<bool>
	 */
	public function hSet(string $key, array $values): Async;

	/**
	 * Set multiple values to hash only if the key exists
	 *
	 * @param array<scalar|null> $values
	 * @return Async<bool>
	 */
	public function hAppend(string $key, array $values): Async;

	/**
	 * @return Async<bool>
	 */
	public function set(string $key, string|float|int|bool|null $value): Async;

	/**
	 * @return Async<string|null>
	 */
	public function get(string $key): Async;

	/**
	 * @return Async<bool>
	 */
	public function del(string $key): Async;

	/**
	 * @return Async<bool>
	 */
	public function delMatch(string $pattern): Async;

	public function flushDatabase(): void;

	/**
	 * @throws TransactionFailedException
	 */
	public function commit(): bool;

}
