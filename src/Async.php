<?php declare(strict_types = 1);

namespace WebChemistry\Cache;

/**
 * @template T
 */
interface Async
{

	/**
	 * @return T
	 */
	public function await(): mixed;

}
