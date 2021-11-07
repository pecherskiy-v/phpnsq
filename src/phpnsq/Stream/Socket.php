<?php

namespace OkStuff\PhpNsq\Stream;

use Exception;

use function strlen;

class Socket
{
    /**
     * @throws Exception
     */
    public static function client($host, $port)
    {
        $socket = stream_socket_client("tcp://$host:$port", $errno, $errstr);
        if (false === $socket) {
            throw new Exception("Could not connect to $host:$port [$errno]:[$errstr]");
        }

        return $socket;
    }

    /**
     * @throws Exception
     */
    public static function sendTo($socket, $buffer): bool|int
    {
        $written = @stream_socket_sendto($socket, $buffer);
        if (0 >= $written) {
            throw new Exception("Could not write " . strlen($buffer) . " bytes to $socket");
        }

        return $written;
    }

    /**
     * @throws Exception
     */
    public static function recvFrom($socket, $length): string
    {
        $buffer = @stream_socket_recvfrom($socket, $length);
        if (empty($buffer)) {
            throw new Exception("Read 0 bytes from $socket");
        }

        return $buffer;
    }

    /**
     * @throws Exception
     */
    public static function select(array &$read, array &$write, $timeout): bool|int
    {
        if ($read || $write) {
            $except = null;

            $available = @stream_select($read, $write, $except, $timeout);
            if ($available > 0) {
                return $available;
            }

            $timestamp = date("Y-m-d H:i:s");
            if (0 === $available) {
                throw new Exception("[$timestamp]stream_select() timeout after $timeout seconds");
            } else {
                throw new Exception("[$timestamp]stream_select() failed");
            }
        }

        $timeout && usleep($timeout);

        return 0;
    }
}
