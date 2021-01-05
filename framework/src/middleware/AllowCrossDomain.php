<?php


namespace iflow\middleware;


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

    /**
     * 允许跨域请求
     * @param $app
     * @param $request
     * @param $response
     * @param array $header
     * @return bool
     */
    public function handle($app, $request, $response, array $header = []): bool
    {
        $header = !empty($header) ? array_merge($this->header, $header) : $this->header;

        $cookieDomain = config('cookie@domain');

        if (!isset($header['Access-Control-Allow-Origin'])) {
            $origin = $request->header['origin'] ?? '';
            if ($origin && ('' == $cookieDomain || strpos($origin, $cookieDomain))) {
                $header['Access-Control-Allow-Origin'] = $origin;
            } else {
                $header['Access-Control-Allow-Origin'] = '*';
            }
        }
        $response -> headers($header);
        return true;
    }
}