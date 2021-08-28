<?php


namespace iflow\session\lib\abstracts;


use iflow\facade\Cache;
use iflow\session\lib\Session;
use iflow\Utils\basicTools;

abstract class sessionAbstracts implements Session
{

    protected array $config;
    protected object $cache;

    public function initializer(array $config): static
    {
        // TODO: Implement initializer() method.
        $this->config = $config;
        $this->cache = Cache::store($this->config['cache_config']);
        return $this;
    }

    public function makeSessionID(): string
    {
        // TODO: Implement makeSessionID() method.
        $number = (new basicTools()) -> make_random_number();
        $host = request() -> getHeader('host');
        $ip = request() -> ip();
        return uniqid($this->config['prefix']) . hash(
                'sha256', "${number}-${host}-${ip}"
            );
    }
}