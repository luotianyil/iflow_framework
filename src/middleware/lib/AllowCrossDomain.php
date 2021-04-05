<?php


namespace iflow\middleware\lib;


/**
 * 跨域请求支持
 */
class AllowCrossDomain
{
    protected array $header = [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age'           => 1800,
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With',
    ];

    public function handle($app, $next, array $header = [])
    {
        $header = !empty($header) ? array_merge($this->header, $header) : $this->header;

        $cookieDomain = config('cookie@domain');

        if (!isset($header['Access-Control-Allow-Origin'])) {
            $origin = $request -> request -> header['origin'] ?? '';
            if ($origin && ('' == $cookieDomain || strpos($origin, $cookieDomain))) {
                $header['Access-Control-Allow-Origin'] = $origin;
            } else {
                $header['Access-Control-Allow-Origin'] = '*';
            }
        }
        response() -> headers($header);
        return $next($app);
    }
}