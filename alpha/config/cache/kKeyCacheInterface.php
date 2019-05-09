<?php
interface vKeyCacheInterface
{
	public function storeKey($key, $ttl=30);
	public function loadKey();
}
