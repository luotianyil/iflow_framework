<?php

namespace iflow\swoole\implement\Tools\Pool\DBPool;

use Smf\ConnectionPool\Connectors\ConnectorInterface;

class DbConnector implements ConnectorInterface {

    protected ?\Closure $checkerConnection = null;

    public function __construct(protected ConnectorInterface|\Closure $connection) {
    }

    /**
     * @param \Closure $checkerConnection
     * @return DbConnector
     */
    public function setCheckerConnection(\Closure $checkerConnection): DbConnector {
        $this->checkerConnection = $checkerConnection;
        return $this;
    }

    public function connect(array $config) {
        // TODO: Implement connect() method.
        if (is_callable($this->connection)) return call_user_func($this->connection, $config);
        return $this -> connection;
    }

    public function disconnect($connection)
    {
        // TODO: Implement disconnect() method.
    }

    public function isConnected($connection): bool {
        // TODO: Implement isConnected() method.
        return call_user_func($this->checkerConnection, $connection);
    }

    public function reset($connection, array $config)
    {
        // TODO: Implement reset() method.
    }

    public function validate($connection): bool
    {
        // TODO: Implement validate() method.
        return true;
    }
}