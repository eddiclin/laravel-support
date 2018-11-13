<?php

namespace Eddic\Support;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class ImageHandler
{
    /**
     * 远程下载图片并保存
     *
     * @param $url
     * @param string $path
     * @return string
     */
    public static function downloadImage($url, $path)
    {
        if (! is_url($url)) {
            return '';
        }

        $guzzle = new Client();
        $response = $guzzle->get($url);

        // 获取扩展名和文件内容
        $contentType = explode('/', $response->getHeader('Content-Type')[0]);
        $content = $response->getBody()->getContents();

        $ext = array_pop($contentType);
        $filename = random_filename($ext);

        $filePath = "{$path}/{$filename}";
        $tmpPath = "tmp/{$filename}";

        // 保存文件
        $localStorage = Storage::disk('local');
        if ($localStorage->put($tmpPath, $content) && is_image(storage_path("app/{$tmpPath}"))) {
            $localStorage->move($tmpPath, "public/{$filePath}");

            return $filePath;
        } else {
            $localStorage->delete($tmpPath);

            return '';
        }
    }
}
