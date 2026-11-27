<?php

namespace app\common\libs\downloader;

/**
 * 下载中心 SSRF 安全校验工具
 */
class SsrfGuard
{
    /**
     * 允许的协议列表
     */
    private const ALLOWED_SCHEMES = ['http', 'https'];

    /**
     * 允许的端口列表
     */
    private const ALLOWED_PORTS = [80, 443];

    /**
     * 禁止的主机名精确匹配列表
     */
    private const BLOCKED_HOSTNAMES = [
        'localhost',
        'ip6-localhost',
        'ip6-loopback',
        'metadata.google.internal',
        'metadata',
    ];

    /**
     * 禁止的主机名后缀列表
     */
    private const BLOCKED_HOST_SUFFIXES = [
        '.localhost',
        '.local',
        '.internal',
        '.localdomain',
    ];

    /**
     * 校验下载 URL 是否安全且仅指向公网 HTTP/HTTPS 地址
     *
     * @param string        $url         目标 URL
     * @param callable|null $dnsResolver 可选 DNS 解析器 function(string $host): array
     * @return array
     */
    public static function validateUrl(string $url, ?callable $dnsResolver = null): array
    {
        $url = trim($url);
        if ($url === '') {
            return createReturn(false, null, 'URL 不能为空');
        }

        if (preg_match('/[\x00-\x1F\x7F\s]/', $url) === 1) {
            return createReturn(false, null, 'URL 包含非法空白或控制字符');
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return createReturn(false, null, 'URL 格式错误');
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (!in_array($scheme, self::ALLOWED_SCHEMES, true)) {
            return createReturn(false, null, '仅允许 http/https 协议');
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return createReturn(false, null, '禁止 URL 携带认证凭据');
        }

        // 防范 authority 中包含 @ 造成解析差异
        $withoutScheme = preg_replace('/^https?:\/\//i', '', $url, 1);
        $authority = (string) preg_split('/[\/?#]/', (string) $withoutScheme, 2)[0];
        if (strpos($authority, '@') !== false || strpos($authority, '\\') !== false) {
            return createReturn(false, null, 'URL 主机格式非法');
        }

        $rawHost = (string) ($parts['host'] ?? '');
        $trimmedRawHost = trim($rawHost, '[]');
        if ($trimmedRawHost === '' || substr($trimmedRawHost, -1) === '.') {
            return createReturn(false, null, 'URL 主机名无效');
        }
        $host = strtolower($trimmedRawHost);
        if ($host === '' || preg_match('/[\s\/\\\\]/', $host) === 1) {
            return createReturn(false, null, 'URL 主机名无效');
        }

        $port = isset($parts['port']) ? (int) $parts['port'] : ($scheme === 'https' ? 443 : 80);
        if (!in_array($port, self::ALLOWED_PORTS, true)) {
            return createReturn(false, null, '仅允许访问 80 与 443 端口');
        }

        if (self::isBlockedHostname($host)) {
            return createReturn(false, null, '禁止访问本地或内网主机');
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            if (!self::isPublicIp($host)) {
                return createReturn(false, null, '禁止访问内网或保留 IP 地址');
            }
            $ips = [$host];
        } else {
            $ips = $dnsResolver !== null ? $dnsResolver($host) : self::resolveHostIps($host);
            $ips = self::normalizeResolvedIps(is_array($ips) ? $ips : []);
            if (empty($ips)) {
                return createReturn(false, null, '域名解析失败，无法获取有效 IP');
            }
            foreach ($ips as $ip) {
                if (!self::isPublicIp($ip)) {
                    return createReturn(false, null, '目标域名解析到内网或保留 IP，已拦截');
                }
            }
        }

        $formattedHost = strpos($host, ':') !== false ? '[' . $host . ']' : $host;
        $isDefaultPort = ($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443);
        $canonicalUrl = $scheme . '://' . $formattedHost . ($isDefaultPort ? '' : ':' . $port)
            . ((string) ($parts['path'] ?? '') !== '' ? $parts['path'] : '/')
            . (isset($parts['query']) ? '?' . $parts['query'] : '')
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');

        return createReturn(true, [
            'url'        => $canonicalUrl,
            'scheme'     => $scheme,
            'host'       => $host,
            'port'       => $port,
            'ips'        => $ips,
            'primary_ip' => $ips[0],
        ], 'ok');
    }

    /**
     * 判断 IP 是否为允许访问的公网地址
     *
     * @param string $ip IP 地址（IPv4 或 IPv6）
     * @return bool
     */
    public static function isPublicIp(string $ip): bool
    {
        $ip = trim($ip, '[] ');
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        $packed = @inet_pton($ip);
        if ($packed === false) {
            return false;
        }

        // 解包 IPv4-mapped / IPv4-compatible IPv6 地址（排除 :: 与 ::1）
        if (strlen($packed) === 16) {
            $mappedV4 = self::extractEmbeddedIpv4($packed);
            if ($mappedV4 !== null) {
                return self::isPublicIpv4($mappedV4);
            }
            return self::isPublicIpv6($ip, $packed);
        }

        return self::isPublicIpv4($ip);
    }

    /**
     * 解析主机名对应的全部 IPv4/IPv6 地址
     *
     * @param string $host 主机名
     * @return array
     */
    public static function resolveHostIps(string $host): array
    {
        $ips = [];
        if (function_exists('dns_get_record')) {
            $records = @dns_get_record($host, DNS_A | DNS_AAAA);
            if (is_array($records)) {
                foreach ($records as $record) {
                    if (!empty($record['ip'])) {
                        $ips[] = $record['ip'];
                    } elseif (!empty($record['ipv6'])) {
                        $ips[] = $record['ipv6'];
                    }
                }
            }
        }

        if (empty($ips)) {
            $v4List = @gethostbynamel($host);
            if (is_array($v4List)) {
                $ips = array_merge($ips, $v4List);
            }
        }

        return self::normalizeResolvedIps($ips);
    }

    /**
     * 根据基础 URL 解析重定向 Location 目标地址
     *
     * @param string $baseUrl  当前请求 URL
     * @param string $location 响应头 Location 值
     * @return string|null
     */
    public static function resolveRedirectUrl(string $baseUrl, string $location): ?string
    {
        $location = trim($location);
        if ($location === '' || preg_match('/[\x00-\x1F\x7F]/', $location) === 1) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $location) === 1) {
            return $location;
        }

        // 若包含非 http/https 的显式 scheme（如 file://、gopher://），保留供 validateUrl 拦截
        if (preg_match('/^[a-z][a-z0-9+\-.]*:/i', $location) === 1) {
            return $location;
        }

        $baseParts = parse_url($baseUrl);
        if (!is_array($baseParts) || empty($baseParts['scheme']) || empty($baseParts['host'])) {
            return null;
        }

        $scheme = strtolower($baseParts['scheme']);
        $host = $baseParts['host'];
        $port = isset($baseParts['port']) ? ':' . $baseParts['port'] : '';
        $origin = $scheme . '://' . $host . $port;
        $basePath = (string) ($baseParts['path'] ?? '');
        if ($basePath === '') {
            $basePath = '/';
        }

        if (strpos($location, '//') === 0) {
            return $scheme . ':' . $location;
        }

        if ($location[0] === '#') {
            $baseQuery = isset($baseParts['query']) ? '?' . $baseParts['query'] : '';
            return $origin . $basePath . $baseQuery . $location;
        }

        if ($location[0] === '?') {
            return $origin . $basePath . $location;
        }

        // 分离相对引用的 path 与 query/fragment
        $suffix = '';
        $refPath = $location;
        if (preg_match('/^([^?#]*)(.*)$/', $location, $matches) === 1) {
            $refPath = $matches[1];
            $suffix = $matches[2];
        }

        if ($refPath !== '' && $refPath[0] === '/') {
            $resolvedPath = self::removeDotSegments($refPath);
        } else {
            $lastSlash = strrpos($basePath, '/');
            $baseDir = $lastSlash !== false ? substr($basePath, 0, $lastSlash + 1) : '/';
            $resolvedPath = self::removeDotSegments($baseDir . $refPath);
        }

        return $origin . $resolvedPath . $suffix;
    }

    /**
     * 按 RFC 3986 §5.2.4 规范化路径中的 . 与 .. 段
     *
     * @param string $path 原始路径
     * @return string
     */
    private static function removeDotSegments(string $path): string
    {
        $input = $path;
        $output = '';

        while ($input !== '') {
            if (strpos($input, '../') === 0) {
                $input = substr($input, 3);
            } elseif (strpos($input, './') === 0) {
                $input = substr($input, 2);
            } elseif (strpos($input, '/./') === 0) {
                $input = '/' . substr($input, 3);
            } elseif ($input === '/.') {
                $input = '/';
            } elseif (strpos($input, '/../') === 0) {
                $input = '/' . substr($input, 4);
                $lastSlash = strrpos($output, '/');
                $output = $lastSlash !== false ? substr($output, 0, $lastSlash) : '';
            } elseif ($input === '/..') {
                $input = '/';
                $lastSlash = strrpos($output, '/');
                $output = $lastSlash !== false ? substr($output, 0, $lastSlash) : '';
            } elseif ($input === '.' || $input === '..') {
                $input = '';
            } else {
                if ($input[0] === '/') {
                    $nextSlash = strpos($input, '/', 1);
                    if ($nextSlash === false) {
                        $output .= $input;
                        $input = '';
                    } else {
                        $output .= substr($input, 0, $nextSlash);
                        $input = substr($input, $nextSlash);
                    }
                } else {
                    $nextSlash = strpos($input, '/');
                    if ($nextSlash === false) {
                        $output .= $input;
                        $input = '';
                    } else {
                        $output .= substr($input, 0, $nextSlash);
                        $input = substr($input, $nextSlash);
                    }
                }
            }
        }

        return $output === '' ? '/' : $output;
    }

    /**
     * 判断主机名是否命中本地或内网域名黑名单
     *
     * @param string $host 规范化后的主机名
     * @return bool
     */
    private static function isBlockedHostname(string $host): bool
    {
        if (in_array($host, self::BLOCKED_HOSTNAMES, true)) {
            return true;
        }

        foreach (self::BLOCKED_HOST_SUFFIXES as $suffix) {
            if (substr($host, -strlen($suffix)) === $suffix) {
                return true;
            }
        }

        return false;
    }

    /**
     * 规范化并去重解析出的 IP 列表
     *
     * @param array $ips 原始 IP 列表
     * @return array
     */
    private static function normalizeResolvedIps(array $ips): array
    {
        $normalized = [];
        foreach ($ips as $ip) {
            if (!is_string($ip)) {
                continue;
            }
            $candidate = trim($ip, '[] ');
            if (filter_var($candidate, FILTER_VALIDATE_IP) !== false) {
                $normalized[$candidate] = true;
            }
        }

        return array_keys($normalized);
    }

    /**
     * 提取 IPv4-mapped / IPv4-compatible IPv6 中的内嵌 IPv4 地址
     *
     * @param string $packed16 16 字节二进制 IPv6
     * @return string|null
     */
    private static function extractEmbeddedIpv4(string $packed16): ?string
    {
        $isV4Mapped = substr($packed16, 0, 12) === (str_repeat("\x00", 10) . "\xff\xff");
        if ($isV4Mapped) {
            $v4 = @inet_ntop(substr($packed16, 12, 4));
            return is_string($v4) ? $v4 : null;
        }

        // ::x.x.x.x（排除 :: 和 ::1）
        if (substr($packed16, 0, 12) === str_repeat("\x00", 12)) {
            $tail = substr($packed16, 12, 4);
            if ($tail !== "\x00\x00\x00\x00" && $tail !== "\x00\x00\x00\x01") {
                $v4 = @inet_ntop($tail);
                return is_string($v4) ? $v4 : null;
            }
        }

        return null;
    }

    /**
     * 判断 IPv4 是否为公网地址
     *
     * @param string $ip IPv4 地址
     * @return bool
     */
    private static function isPublicIpv4(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }

        $long = ip2long($ip);
        if ($long === false) {
            return false;
        }

        $blockedCidrs = [
            ['0.0.0.0', 8],       // Current network
            ['10.0.0.0', 8],      // Private
            ['100.64.0.0', 10],   // CGNAT & Alibaba Cloud metadata (100.100.100.200)
            ['127.0.0.0', 8],     // Loopback
            ['169.254.0.0', 16],  // Link-local & Cloud metadata (169.254.169.254)
            ['172.16.0.0', 12],   // Private
            ['192.0.0.0', 24],    // IETF Protocol Assignments
            ['192.0.2.0', 24],    // TEST-NET-1
            ['192.168.0.0', 16],  // Private
            ['198.18.0.0', 15],   // Benchmarking
            ['198.51.100.0', 24], // TEST-NET-2
            ['203.0.113.0', 24],  // TEST-NET-3
            ['224.0.0.0', 4],     // Multicast
            ['240.0.0.0', 4],     // Reserved / Broadcast
        ];

        foreach ($blockedCidrs as [$network, $prefix]) {
            $netLong = ip2long($network);
            $mask = $prefix === 0 ? 0 : (~((1 << (32 - $prefix)) - 1));
            if (($long & $mask) === ($netLong & $mask)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 判断 IPv6 是否为公网地址
     *
     * @param string $ip       IPv6 字符串
     * @param string $packed16 16 字节二进制表示
     * @return bool
     */
    private static function isPublicIpv6(string $ip, string $packed16): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }

        $blockedPrefixes = [
            ['::', 128],         // Unspecified
            ['::1', 128],        // Loopback
            ['::ffff:0:0', 96],  // IPv4-mapped
            ['64:ff9b::', 96],   // NAT64
            ['100::', 64],       // Discard-Only
            ['2001:db8::', 32],  // Documentation
            ['fc00::', 7],       // Unique Local Address (includes fd00:ec2::254)
            ['fe80::', 10],      // Link-local
            ['ff00::', 8],       // Multicast
        ];

        foreach ($blockedPrefixes as [$network, $bits]) {
            if (self::ipv6MatchesCidr($packed16, $network, $bits)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 校验 16 字节 IPv6 二进制地址是否命中指定前缀
     *
     * @param string $packed16 16 字节二进制 IPv6
     * @param string $network  前缀网络地址
     * @param int    $bits     前缀长度
     * @return bool
     */
    private static function ipv6MatchesCidr(string $packed16, string $network, int $bits): bool
    {
        $netPacked = @inet_pton($network);
        if ($netPacked === false || strlen($netPacked) !== 16) {
            return false;
        }

        $fullBytes = intdiv($bits, 8);
        $remBits = $bits % 8;

        if ($fullBytes > 0 && substr($packed16, 0, $fullBytes) !== substr($netPacked, 0, $fullBytes)) {
            return false;
        }

        if ($remBits > 0) {
            $mask = (0xFF << (8 - $remBits)) & 0xFF;
            if ((ord($packed16[$fullBytes]) & $mask) !== (ord($netPacked[$fullBytes]) & $mask)) {
                return false;
            }
        }

        return true;
    }
}
