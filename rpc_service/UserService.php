<?php

namespace app\rpc_service;

class UserService extends \iflow\swoole\implement\Client\Rpc\implement\RpcClient implements \app\Rpc\Interfaces\UserService {

    

    public function getUserInfo(string $name, string $desc): string { 
        return $this -> request(__FUNCTION__, func_get_args()); 
    }

public function get(): mixed { 
        return $this -> request(__FUNCTION__, func_get_args()); 
    }


}
