<?php

use Tester\Assert;
use WebChemistry\Cache\RedisCache;

require __DIR__ . '/../bootstrap.php';

$redis = new Redis();
$redis->connect('localhost', 6555);

$rootCache = new RedisCache($redis);
$cache = $rootCache->namespace('test');

afterEachTest(function () use ($rootCache) {
	$rootCache->flushDatabase();
});

test('set and get', function () use ($cache): void {
	$foo = $cache->set('foo', 'foo');
	$bar = $cache->set('bar', 'bar');

	Assert::same(true, $foo->await());
	Assert::same(true, $bar->await());

	$foo = $cache->get('foo');
	$bar = $cache->get('bar');

	Assert::same('foo', $foo->await());
	Assert::same('bar', $bar->await());
});

test('keys', function () use ($cache): void {
	$cache->set('foo', 'foo');
	$cache->set('bar', 'bar');

	Assert::same(['test:foo', 'test:bar'], $cache->keys('*')->await());
});

test('hash', function () use ($cache): void {
	$set = $cache->hSet('foo', ['foo' => 'foo', 'bar' => 'bar']);

	Assert::same(['foo' => 'foo', 'bar' => 'bar'], $cache->hGetAll('foo')->await());
	Assert::same(true, $set->await());
});

test('hash append', function () use ($cache): void {
	$cache->hSet('foo', ['foo' => 'foo', 'bar' => 'bar']);
	$append = $cache->hAppend('foo', ['append' => 'append']);

	Assert::true($append->await());
	Assert::same(['foo' => 'foo', 'bar' => 'bar', 'append' => 'append'], $cache->hGetAll('foo')->await());

	$append = $cache->hAppend('bar', ['append' => 'append']);

	Assert::false($append->await());
	Assert::same([], $cache->hGetAll('bar')->await());
});
