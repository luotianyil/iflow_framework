<?php


namespace iflow\middleware\DefaultMiddleware;


use iflow\App;
use iflow\Container\implement\generate\exceptions\InvokeClassException;

/**
 * 跨域请求支持
 */
class AllowCrossDomain {

    protected array $header = [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age'           => 1800,
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With',
    ];

    protected array $AllowOrigin = [ '*' ];

    /**
     * @throws InvokeClassException
     */
    public function handle(App $app, $next, array $header = [], array $AllowOrigin = [ '*' ]) {
        $this->header = !empty($header) ? array_merge($this->header, $header) : $this->header;
        $this->AllowOrigin = $AllowOrigin;

        $origin = request() -> getHeader('origin');
        if (!$this->checkAllowOrigin($origin)) {
            config('cookie@domain', function ($cookieDomain) use ($origin) {
                $cookieDomain = $cookieDomain ?: '';
                $this->header['Access-Control-Allow-Origin'] =
                    $origin && ('' == $cookieDomain || strpos($origin, $cookieDomain))? $origin : '';
            });
        }
        response() -> headers($this->header);
        return $next($app);
    }

    /**
     * 检查请求域名
     * @param string $origin
     * @return bool
     */
    protected function checkAllowOrigin(string $origin): bool {
        if (in_array('*', $this->AllowOrigin) || in_array($origin, $this->AllowOrigin)) {
            $this->header['Access-Control-Allow-Origin'] = $origin;
        }
        return false;
    }
}