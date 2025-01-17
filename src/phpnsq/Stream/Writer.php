<?php

namespace OkStuff\PhpNsq\Stream;

use function strlen;
use function count;
use JetBrains\PhpStorm\Pure;

class Writer
{
    public const MAGIC_V2 = "  V2";

    public static function magic(): string
    {
        return self::MAGIC_V2;
    }

    public static function pub($topic, $body): string
    {
        $cmd = self::command("PUB", $topic);
        $size = IntPacker::uInt32(strlen($body), true);

        return $cmd . $size . $body;
    }

    private static function command($action, ...$params): string
    {
        $str = $action;
        if (count($params) >= 1) {
            $str .= sprintf(" %s", implode(" ", $params));
        }
        $str .= "\n";

        return $str;
    }

    public static function mpub($topic, array $bodies): string
    {
        $cmd = self::command("MPUB", $topic);
        $num = IntPacker::uInt32(count($bodies), true);
        $mb = implode(
            array_map(function ($body) {
                return IntPacker::uint32(strlen($body), true) . $body;
            }, $bodies)
        );
        $size = IntPacker::uInt32(strlen($num . $mb), true);

        return $cmd . $size . $num . $mb;
    }

    public static function dpub($topic, $deferTime, $body): string
    {
        $cmd = self::command("DPUB", $topic, $deferTime);
        $size = IntPacker::uInt32(strlen($body), true);

        return $cmd . $size . $body;
    }

    #[Pure] public static function sub($topic, $channel): string
    {
        return self::command("SUB", $topic, $channel);
    }

    #[Pure] public static function rdy($count): string
    {
        return self::command("RDY", $count);
    }

    #[Pure] public static function fin($id): string
    {
        return self::command("FIN", $id);
    }

    #[Pure] public static function req($id, $timeout): string
    {
        return self::command("REQ", $id, $timeout);
    }
    
    #[Pure] public static function touch($id): string
    {
        return self::command("TOUCH", $id);
    }

    #[Pure] public static function cls(): string
    {
        return self::command("CLS");
    }

    #[Pure] public static function nop(): string
    {
        return self::command("NOP");
    }

    /**
     * @throws \JsonException
     */
    public static function identify(array $arr): string
    {
        $cmd = self::command("IDENTIFY");
        $body = json_encode($arr, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT);
        $size = IntPacker::uInt32(strlen($body), true);

        return $cmd . $size . $body;
    }

    public static function auth($secret): string
    {
        $cmd = self::command("AUTH");
        $size = IntPacker::uInt32(strlen($secret), true);

        return $cmd . $size . $secret;
    }
}
