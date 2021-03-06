<?php

namespace Samerior\LaravelSidebar\Infrastructure;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Samerior\LaravelSidebar\Contracts\Sidebar;
use Samerior\LaravelSidebar\Contracts\SidebarResolver;

/**
 * Class UserBasedCacheResolver
 * @package Samerior\LaravelSidebar\Infrastructure
 */
class UserBasedCacheResolver implements SidebarResolver
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ContainerResolver
     */
    protected $resolver;

    /**
     * @var Guard
     */
    protected $guard;

    /**
     * @param ContainerResolver $resolver
     * @param Cache $cache
     * @param Guard $guard
     * @param Config $config
     */
    public function __construct(ContainerResolver $resolver, Cache $cache, Guard $guard, Config $config)
    {
        $this->cache = $cache;
        $this->resolver = $resolver;
        $this->guard = $guard;
        $this->config = $config;
    }

    /**
     * @param $name
     *
     * @return Sidebar
     * @throws \Samerior\LaravelSidebar\Exceptions\CacheTagsNotSupported
     */
    public function resolve($name)
    {
        if ((new SupportsCacheTags())->isSatisfiedBy($this->cache)) {
            $userId = $this->guard->check() ? $this->guard->user()->getAuthIdentifier() : null;
            $duration = $this->config->get('sidebar.cache.duration');

            return $this->cache->tags(CacheKey::get($name))->remember(CacheKey::get($name, $userId), $duration, function () use ($name) {
                return $this->resolver->resolve($name);
            });
        }
    }
}
