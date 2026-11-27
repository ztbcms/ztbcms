<?php
/**
 * Author: cycle_3
 */

namespace app\common\libs\downloader;

use app\admin\service\AdminConfigService;
use app\common\model\upload\AttachmentModel;
use Error;
use Exception;

/**
 * 文件下载工具
 */
class DownloaderTool
{
    /**
     * 最大允许重定向次数
     */
    const MAX_REDIRECTS = 3;

    static function getFileExtensionByUrl($url)
    {
        $path = parse_url((string) $url, PHP_URL_PATH);
        $target = is_string($path) && $path !== '' ? $path : (string) $url;
        $path_info = pathinfo($target);
        if (isset($path_info['extension'])) {
            return strtolower($path_info['extension']);
        }
        return '';
    }

    /**
     * 根据文件名称获取扩展名
     * @param $file_name
     * @return mixed|string|void
     */
    static function getFileExtensionByFileName($file_name)
    {
        $path_info = pathinfo($file_name);
        if (isset($path_info['extension'])) {
            return strtolower($path_info['extension']);
        }
        return '';
    }

    /**
     * 在线文件下载
     * @param string $url
     * @param string $file_name
     * @return array
     */
    static function downloaderFile(string $url, string $file_name = ''): array
    {
        // 文件类型获取 文件名称 > URL 中获取
        $file_extension = self::getFileExtensionByFileName($file_name);
        if (empty($file_extension)) {
            $file_extension = self::getFileExtensionByUrl($url);
        }
        $file_extension = strtolower(ltrim((string) $file_extension, '.'));
        if (empty($file_name)) {
            $file_name = md5($url) . '.' . $file_extension;
        }
        if (in_array($file_extension, ['jpg', 'gif', 'png', 'jpeg', 'bmp'], true)) {
            //图片
            return self::doDownloadFile($url, $file_name, $file_extension, AttachmentModel::MODULE_IMAGE);
        }
        if (in_array($file_extension, ['mp4'], true)) {
            //视频
            return self::doDownloadFile($url, $file_name, $file_extension, AttachmentModel::MODULE_VIDEO);
        }
        if (in_array($file_extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'], true)) {
            //文件
            return self::doDownloadFile($url, $file_name, $file_extension, AttachmentModel::MODULE_FILE);
        }
        return createReturn(false, null, '文件类型暂不支持');
    }


    /**
     * 文件下载实现
     * @param string        $url
     * @param string        $filename
     * @param string        $file_extension
     * @param string        $module
     * @param callable|null $dnsResolver 可选 DNS 解析器（用于测试注入）
     * @param callable|null $httpFetcher 可选 HTTP 请求执行器（用于测试注入）
     * @return array
     */
    static function doDownloadFile(
        string $url = '',
        string $filename = '',
        $file_extension = '',
        string $module = '',
        ?callable $dnsResolver = null,
        ?callable $httpFetcher = null
    ) {
        try {
            $url = trim($url);
            $filename = trim($filename);
            if ($filename === '' || basename($filename) !== $filename || strpos($filename, '..') !== false) {
                return createReturn(false, [], '文件名非法');
            }

            $currentUrl = $url;
            $redirectCount = 0;
            $resource = null;

            while (true) {
                $guardRes = SsrfGuard::validateUrl($currentUrl, $dnsResolver);
                if (empty($guardRes['status'])) {
                    return createReturn(false, [], (string) ($guardRes['msg'] ?? '目标地址不安全'));
                }

                $targetMeta = $guardRes['data'];
                $targetUrl = (string) ($targetMeta['url'] ?? $currentUrl);
                if ($httpFetcher !== null) {
                    $fetchRes = $httpFetcher($targetUrl, $targetMeta);
                } else {
                    $fetchRes = self::executeCurlRequest($targetUrl, $targetMeta);
                }

                if (empty($fetchRes['status'])) {
                    return createReturn(false, [], (string) ($fetchRes['msg'] ?? '下载请求失败'));
                }

                $httpCode = (int) ($fetchRes['data']['http_code'] ?? 0);
                if (in_array($httpCode, [301, 302, 303, 307, 308], true)) {
                    if ($redirectCount >= self::MAX_REDIRECTS) {
                        return createReturn(false, [], '重定向次数过多');
                    }
                    $location = (string) ($fetchRes['data']['location'] ?? '');
                    $nextUrl = SsrfGuard::resolveRedirectUrl($currentUrl, $location);
                    if ($nextUrl === null) {
                        return createReturn(false, [], '重定向目标地址无效');
                    }
                    $currentUrl = $nextUrl;
                    $redirectCount++;
                    continue;
                }

                if ($httpCode < 200 || $httpCode >= 300) {
                    return createReturn(false, [], 'HTTP Error: ' . $httpCode);
                }

                $resource = (string) ($fetchRes['data']['body'] ?? '');
                break;
            }

            // 保存路径
            $save_path_base = '/downloader/' . substr(md5($url), 0, 2) . '/';
            $save_path = app()->getRootPath() . 'public' . $save_path_base;
            $file_path = $save_path . $filename;
            $file_path_base = $save_path_base . $filename;
            if (!is_dir($save_path)) {
                mkdir($save_path, 0777, true); //创建目录
            }

            //w+ 读写方式打开，将文件指针指向文件头并将文件大小截为零。如果文件不存在则尝试创建之。
            $file = fopen($file_path, 'w+');
            if ($file === false) {
                return createReturn(false, [], '文件写入失败');
            }
            fwrite($file, $resource);
            fclose($file);
            // 访问地址
            $host = AdminConfigService::getInstance()->getConfig('downloader_domain')['data'];
            $file_url = rtrim($host, '/') . $file_path_base;
            $file_thumb = '';
            if (in_array($file_extension, ['jpg', 'gif', 'png', 'jpeg'], true)) {
                //图片
                $file_thumb = rtrim($host, '/') . $file_path_base;
            }
            if (in_array($file_extension, ['mp4'], true)) {
                //视频
                $file_thumb = rtrim($host, '/') . '/statics/admin/upload/video.png';
            }
            if (in_array($file_extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'], true)) {
                //文件
                $file_thumb = rtrim($host, '/') . '/statics/admin/upload/file.png';
            }
            return createReturn(true, [
                'module' => $module,
                'file_name' => $filename,
                'file_path' => $file_path_base,
                'file_url' => $file_url,
                'file_thumb' => $file_thumb,
                'file_size' => filesize($file_path),
                'file_ext' => $file_extension,
                'file_hash' => hash_file('md5', $file_path)
            ]);
        } catch (Exception | Error $e) {
            return createReturn(false, [], $e->getMessage());
        }
    }

    /**
     * 使用 cURL 发起单跳受保护的 HTTP/HTTPS 请求（禁用自动重定向并锁定解析 IP）
     *
     * @param string $url        目标 URL
     * @param array  $targetMeta SsrfGuard 解析元数据
     * @return array
     */
    private static function executeCurlRequest(string $url, array $targetMeta): array
    {
        $ch = curl_init();
        if ($ch === false) {
            return createReturn(false, [], 'CURL Error:init failed');
        }

        $location = '';
        $targetUrl = (string) ($targetMeta['url'] ?? $url);
        curl_setopt($ch, CURLOPT_URL, $targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
        curl_setopt($ch, CURLOPT_PROXY, '');
        if (defined('CURLOPT_NOPROXY')) {
            curl_setopt($ch, CURLOPT_NOPROXY, '*');
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (defined('CURLPROTO_HTTP') && defined('CURLPROTO_HTTPS')) {
            curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        }

        // 锁定已校验的公网 IP，防范 DNS Rebinding
        $host = (string) ($targetMeta['host'] ?? '');
        $port = (int) ($targetMeta['port'] ?? 80);
        $primaryIp = (string) ($targetMeta['primary_ip'] ?? '');
        if ($host !== '' && $primaryIp !== '' && filter_var($host, FILTER_VALIDATE_IP) === false) {
            $formattedIp = strpos($primaryIp, ':') !== false ? '[' . $primaryIp . ']' : $primaryIp;
            if (!curl_setopt($ch, CURLOPT_RESOLVE, ["{$host}:{$port}:{$formattedIp}"])) {
                curl_close($ch);
                return createReturn(false, [], 'CURL Error:failed to bind resolved IP');
            }
        }

        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, string $headerLine) use (&$location): int {
            if (stripos($headerLine, 'Location:') === 0) {
                $location = trim(substr($headerLine, 9));
            }
            return strlen($headerLine);
        });

        $body = curl_exec($ch);
        if ($body === false) {
            $curlError = curl_error($ch);
            curl_close($ch);
            return createReturn(false, [], 'CURL Error:' . $curlError);
        }

        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return createReturn(true, [
            'http_code' => $httpCode,
            'location'  => $location,
            'body'      => (string) $body,
        ], 'ok');
    }
}