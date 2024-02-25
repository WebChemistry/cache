<?php declare(strict_types = 1);

namespace WebChemistry\Cache;

/**
 * @template-covariant T
 */
interface Async
{

	/**
	 * @return T
	 */
	public function await(): mixed;

}
