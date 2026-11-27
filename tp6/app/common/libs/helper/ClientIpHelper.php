<?php

namespace app\common\libs\helper;

use think\facade\Config;

/**
 * 客户端真实 IP 解析工具（防范 X-Forwarded-For 等请求头伪造）
 */
class ClientIpHelper
{
    /**
     * 获取当前请求的真实客户端 IP
     *
     * 仅当配置了受信反向代理 IP（proxy_server_ips）且直连 REMOTE_ADDR 命中受信代理时，
     * 才从配置的代理转发头（proxy_client_ip_headers）中解析客户端 IP。
     *
     * @param mixed $request 请求实例，默认使用当前应用请求
     * @return string
     */
    public static function getClientIp($request = null): string
    {
        $request = $request ?: request();
        $remoteAddr = trim((string) $request->server('REMOTE_ADDR', ''));
        if ($remoteAddr === '') {
            $fallbackIp = trim((string) $request->ip());
            return filter_var($fallbackIp, FILTER_VALIDATE_IP) !== false ? $fallbackIp : '0.0.0.0';
        }

        if (filter_var($remoteAddr, FILTER_VALIDATE_IP) === false) {
            return '0.0.0.0';
        }

        $trustedProxies = Config::get('admin.proxy_server_ips', []);
        $proxyHeaders = Config::get('admin.proxy_client_ip_headers', []);
        if (!is_array($trustedProxies) || empty($trustedProxies) || !is_array($proxyHeaders) || empty($proxyHeaders)) {
            return $remoteAddr;
        }

        if (!self::ipMatchesList($remoteAddr, $trustedProxies)) {
            return $remoteAddr;
        }

        foreach ($proxyHeaders as $header) {
            if (!is_string($header) || trim($header) === '') {
                continue;
            }
            $serverKey = self::normalizeServerHeaderKey($header);
            $rawValue = (string) $request->server($serverKey, '');
            if ($rawValue === '') {
                continue;
            }

            $candidates = array_reverse(array_map('trim', explode(',', $rawValue)));
            $firstValidIp = null;
            foreach ($candidates as $candidate) {
                if (filter_var($candidate, FILTER_VALIDATE_IP) === false) {
                    continue;
                }
                if ($firstValidIp === null) {
                    $firstValidIp = $candidate;
                }
                // 从右向左跳过受信代理 IP，取第一个非受信代理的真实客户端 IP
                if (!self::ipMatchesList($candidate, $trustedProxies)) {
                    return $candidate;
                }
            }
            if ($firstValidIp !== null) {
                return $firstValidIp;
            }
        }

        return $remoteAddr;
    }

    /**
     * 规范化 HTTP 请求头在 $_SERVER 中的键名
     *
     * @param string $header 请求头名称
     * @return string
     */
    private static function normalizeServerHeaderKey(string $header): string
    {
        $upper = strtoupper(str_replace('-', '_', trim($header)));
        if (strpos($upper, 'HTTP_') === 0) {
            return $upper;
        }
        return 'HTTP_' . $upper;
    }

    /**
     * 判断给定 IP 是否命中 IP / CIDR 列表
     *
     * @param string $ip       待校验 IP
     * @param array  $cidrList IP 或 CIDR 列表
     * @return bool
     */
    private static function ipMatchesList(string $ip, array $cidrList): bool
    {
        $ipBin = @inet_pton($ip);
        if ($ipBin === false) {
            return false;
        }

        foreach ($cidrList as $entry) {
            if (!is_string($entry) || trim($entry) === '') {
                continue;
            }
            $entry = trim($entry);
            $parts = explode('/', $entry, 2);
            $netIp = $parts[0];
            $netBin = @inet_pton($netIp);
            if ($netBin === false || strlen($netBin) !== strlen($ipBin)) {
                continue;
            }

            $maxBits = strlen($ipBin) * 8;
            $prefix = isset($parts[1]) ? (int) $parts[1] : $maxBits;
            if ($prefix < 0 || $prefix > $maxBits) {
                continue;
            }

            $fullBytes = intdiv($prefix, 8);
            $remBits = $prefix % 8;

            if ($fullBytes > 0 && substr($ipBin, 0, $fullBytes) !== substr($netBin, 0, $fullBytes)) {
                continue;
            }
            if ($remBits > 0) {
                $mask = (0xFF << (8 - $remBits)) & 0xFF;
                if ((ord($ipBin[$fullBytes]) & $mask) !== (ord($netBin[$fullBytes]) & $mask)) {
                    continue;
                }
            }
            return true;
        }

        return false;
    }
}
