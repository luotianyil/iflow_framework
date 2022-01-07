<?php


namespace iflow\Swoole\dht\lib\utils;


use iflow\Swoole\dht\lib\config;
use iflow\Swoole\dht\lib\utils\coding\client\Decode;
use iflow\Swoole\dht\lib\utils\coding\client\Encode;
use Swoole\Client;

class Metadata
{
    CONST BT_PROTOCOL = 'BitTorrent protocol';
    CONST BT_MSG_ID = 20;
    CONST EXT_HANDSHAKE_ID = 0;
    CONST PIECE_LENGTH = 16384;

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    public function __construct(
        protected Client $client,
        protected string $infoHash,
        protected config $config
    ){}

    public function downloadMetaData(): bool|array
    {
        $packet = $this->sendHandshake();
        if ($packet === false) return false;
        $checkHandshake = $this->checkHandshake($packet);

        if ($checkHandshake === false) return false;

        $packet = $this->sendExtHandshake();

        if ($packet === false) return false;
        $utMetadata = $this->getUtMetadata($packet);
        $metaDataSize = $this->getMetadataSize($packet);
        if ($metaDataSize > self::PIECE_LENGTH * 1000) return false;

        $metaData = [];
        $piecesNum = ceil($metaDataSize / (self::PIECE_LENGTH));

        for ($i = 0; $i < $piecesNum; $i++) {
             $requestMeta = $this->requestMetadata($utMetadata, $i);
             if ($requestMeta === false) return false;
             $packet = $this->recvAll();

             if ($packet === false) return false;
             $ee = substr($packet, 0, strpos($packet, 'ee') + 2);
             $dict = Decode::decode(substr($ee, strpos($packet, 'd')));

             if (isset($dict['msg_type']) && $dict['msg_type'] != 1) return false;

             $_metaData = substr($packet,strpos($packet,"ee") + 2);

            if(strlen($_metaData) > self::PIECE_LENGTH) return false;

            $metaData[] = $_metaData;
        }

        $metadata = implode('', $metaData);
        $metadata = Decode::decode($metadata);

        $infoHash = strtoupper(bin2hex($this->infoHash));
        $data = [];

        if (!isset($metadata['name']) && $metadata['name'] === '') return false;

        $data['name'] = '';
        $data['infohash'] = $infoHash;
        $data['length'] = $metadata['length'] ?? 0;
        $data['piece_length'] = $metadata['piece length'] ?? 0;

        return $data;
    }

    public function requestMetadata($ut_metadata, $piece)
    {
        $msg = chr(self::BT_MSG_ID)
            .chr($ut_metadata)
            .Encode::encode([
                'msg_type' => 0,
                'piece' => $piece
            ]);

        $len = pack("I", strlen($msg));

        return $this->client -> send($len . $msg);
    }

    public function recvAll(): bool|string {
        $len = $this->client -> recv(4, true);
        if ($len === false) return false;

        if (strlen($len) != 4) return false;
        $len = intval(unpack('N', $len)[1]);

        if ($len === 0) return false;

        if ($len > self::PIECE_LENGTH * 1000) return false;

        $data = '';

        while (true) {
            if ($len > 8192) {
                if ($recv = $this->client -> recv(8192, true) === false) {
                    return false;
                }
                $data .= $recv;
                $len = $len - 8192;
            } else {
                if ($recv = $this->client -> recv(8192, true) === false) {
                    return false;
                }
                $data .= $recv;
                break;
            }
        }

        return $data;
    }

    public function sendHandshake()
    {
        $btHeader = chr(strlen(self::BT_PROTOCOL)) . self::BT_PROTOCOL;
        $exBytes = "\x00\x00\x00\x00\x00\x10\x00\x00";
        $peerId = $this->config -> genNodeId();
        $packet = $btHeader . $exBytes . $this->infoHash . $peerId;
        return $this->sendToRecv($packet);
    }

    public function checkHandshake($packet): bool
    {
        $btHeaderLen = ord(substr($packet, 0, 1));
        $packet = substr($packet, 1);

        if ($btHeaderLen !== strlen(self::BT_PROTOCOL)) return false;
        $btHeader = substr($packet, 0, $btHeaderLen);
        $packet = substr($packet, $btHeaderLen);

        if ($btHeader != self::BT_PROTOCOL) return false;

        $packet = substr($packet, 8);
        $infoHash = substr($packet, 0, 20);

        if ($infoHash !== $this->infoHash) return false;

        return true;
    }

    public function sendExtHandshake()
    {
        $msg = chr(self::BT_MSG_ID).
            chr(self::EXT_HANDSHAKE_ID)
            .Encode::encode([
                'm' => [
                    'ut_metadata' => 1
                ]
            ]);

        $len = pack('I', strlen($msg));

        if (!pack('L', 1) === pack('N', 1)) {
            $len = strrev($len);
        }

        $msg = $len . $msg;
       return $this->sendToRecv($msg);
    }

    public function getUtMetadata($data): int
    {
        $utMetaData = '_metadata';
        $index = strpos($data, $utMetaData) + strlen($utMetaData) + 1;
        return intval($data[$index]);
    }

    public function getMetadataSize($data): int
    {
        $metaDataSize = 'metadata_size';
        $start = strpos($data, $metaDataSize) + strlen($metaDataSize) + 1;
        $data = substr($data, $start);
        $eIndex = strpos($data, "e");
        return intval(substr($data, 0, $eIndex));
    }

    protected function sendToRecv($msg)
    {
        $send = $this->client -> send($msg);
        if ($send === false) return false;

        $data = $this->client -> recv(4096, 0);
        if ($data === false) return false;
        return $data;
    }

}