<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;
use Vinkla\Hashids\Facades\Hashids;

if (! function_exists('maybe_json_encode')) {
    /**
     * JSON encode data which is array or object.
     *
     * @param mixed $value Data that might be JSON encoded.
     * @return mixed A scalar data.
     */
    function maybe_json_encode($value)
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        return $value;
    }
}

if (! function_exists('maybe_json_decode')) {
    /**
     * JSON decode value only if it was JSON encoded.
     *
     * @param string $value Maybe JSON encoded value, if is needed.
     * @param bool $assoc
     * @return mixed
     */
    function maybe_json_decode($value, $assoc = false)
    {
        if (! is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, $assoc);
        if (json_last_error() == JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
            return $decoded;
        } else {
            // Reset the JSON error code:
            json_decode('[]');

            return $value;
        }
    }
}

if (! function_exists('is_json')) {
    function is_json($string)
    {
        if (! is_string($string)) {
            return false;
        }

        $array = json_decode($string, true);

        // 注意：当 $string 为 'false' 时，json_decode 之后亦为 false，但这种情况很少见，可忽略
        return json_last_error() == JSON_ERROR_NONE ? $array : false;
    }
}

if (! function_exists('checked')) {
    function checked($checked, $current = true)
    {
        return html_checked_selected($checked, $current, 'checked');
    }
}

if (! function_exists('selected')) {
    function selected($selected, $current = true)
    {
        return html_checked_selected($selected, $current, 'selected');
    }
}

if (! function_exists('disabled')) {
    function disabled($disabled, $current = true)
    {
        return html_checked_selected($disabled, $current, 'disabled');
    }
}

if (! function_exists('html_checked_selected')) {
    function html_checked_selected($compare, $current, $type)
    {
        return (string) $compare === (string) $current ? " $type='$type'" : '';
    }
}

if (! function_exists('str_explode_trim')) {
    function str_explode_trim($value)
    {
        return array_map('trim', explode("\n", $value));
    }
}

if (! function_exists('is_phone')) {
    function is_phone($phone)
    {
        return $phone ? boolval(preg_match('/^1[34578]\d{9}$/', $phone)) : false;
    }
}

if (! function_exists('random_number')) {
    /**
     * 生成随机数字字符串
     *
     * @param int $length 随机数字串长度
     * @return string
     */
    function random_number($length = 16)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $number = strval(mt_rand(1000000000, 9999999999));

            $string .= substr($number, 0, $size);
        }

        return $string;
    }
}

if (! function_exists('random_float')) {
    /**
     * 随机浮点数
     *
     * @param float $min
     * @param float $max
     * @return float
     */
    function random_float($min, $max)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}

if (! function_exists('str_contains_i')) {
    /**
     * 判断字符串是否被包含（不区分大小写）
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    function str_contains_i($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_stripos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('multi_explode')) {
    /**
     * 使用多个字符串分割另一个字符串
     *
     * @param array $delimiters
     * @param string $string
     * @return array
     */
    function multi_explode($delimiters, $string)
    {
        $replaced = str_replace($delimiters, $delimiters[0], $string);

        return explode($delimiters[0], $replaced);
    }
}

if (! function_exists('add_query_arg')) {
    /**
     * 给链接添加查询参数
     *
     * @param array $args
     * @param string $uri
     * @return string
     */
    function add_query_arg($args, $uri = '')
    {
        if (empty($uri)) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        $frag = strval(strstr($uri, '#'));
        if ($frag) {
            $uri = substr($uri, 0, -strlen($frag));
        }

        parse_str(parse_url($uri, PHP_URL_QUERY), $qs);
        foreach ($args as $k => $v) {
            $qs[$k] = $v;
        }

        foreach ($qs as $k => $v) {
            if ($v === false) {
                unset($qs[$k]);
            }
        }

        if (! $base = strstr($uri, '?', true)) {
            $base = $uri;
        }

        $query = http_build_query($qs);
        if ($query) {
            $base .= '?';
        }

        return $base . $query . $frag;
    }
}

if (! function_exists('contain_urls')) {
    /**
     * 获取字符串中包含的 url
     *
     * @param string $content
     * @return array
     */
    function contain_urls($content)
    {
        // url正则匹配，必须以 http 开头
        $pattern = "/https?\:\/\/[\w\-]+(?:\.[\w\-]+)+(?:[\w\-\.,@?^=%&:\/~\+\|#]*)/";

        $matched = preg_match_all($pattern, $content, $matches);

        $urls = [];
        if ($matched) {
            foreach ($matches[0] as $url) {
                // 检查顶级域名，只能是字母
                if (preg_match('/\\.[a-zA-Z]+$/', parse_url($url, PHP_URL_HOST))) {
                    $urls[] = $url;
                }
            }
        }

        return $urls;
    }
}

if (! function_exists('match_urls')) {
    /**
     * 从字符串中匹配出链接
     *
     * 测试例子：
     * $content = ' a.a?%.b 测试 . hello  1.2  aahttp://www.domain.com/?a=b   www.domain.com/a';
     *
     * @param string $content
     * @return array
     */
    function match_urls($content)
    {
        // 将非 url 字符替换为空格
        $content = preg_replace('/[^a-zA-Z0-9\\.:_\\/\\?;=&%]/', ' ', " {$content} ");

        // 将前面有空格的特殊字符串替换为空格
        $content = preg_replace('/[ ]+[^a-zA-Z0-9]+/', ' ', " {$content} ");

        // 将两边有空格且不含"."号的字符串替换为空格
        $content = preg_replace('/[ ]+[^\\.]+[ ]+/', ' ', " {$content} ");

        $array = array_unique(explode(' ', trim($content)));

        $matches = [];
        foreach ($array as $item) {
            $url = preg_match('#https?\:\/\/#', $item) ? $item : "http://{$item}";
            $urls = contain_urls($url);

            if (empty($urls)) {
                continue;
            }

            if ($url == $item) {    // $item 自带 http 协议头
                $matches[$urls[0]] = $urls[0];
            } else {
                $matches[$item] = $urls[0];
            }
        }

        return $matches;
    }
}

if (! function_exists('replace_path_separator')) {
    /**
     * 替换路径分隔符
     *
     * @param string $path
     * @param string $type dir or url
     * @return mixed
     */
    function replace_path_separator($path, $type = 'dir')
    {
        if ('url' == $type) {
            return str_replace('\\', '/', $path);
        }

        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}

if (! function_exists('get_absolute_path')) {
    /**
     * 去掉path中的 '/./', '/../' 以及多余的 '/'
     *
     * @param string $path
     * @return string
     */
    function get_absolute_path($path)
    {
        $path = replace_path_separator($path);
        $first_slash = substr($path, 0, 1) == DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : '';

        $absolutes = [];
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($parts as $part) {
            if ('' === $part || '.' == $part) {
                continue;
            }

            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        return $first_slash . implode(DIRECTORY_SEPARATOR, $absolutes);
    }
}

if (! function_exists('is_realpath')) {
    /**
     * 检查path是否为规范化的绝对路径名
     *
     * @param string $path
     * @param bool $strict path路径是否要真实存在
     * @return bool
     */
    function is_realpath($path, $strict = false)
    {
        $path = replace_path_separator($path);

        $real = realpath($path) == $path;
        if ($real || $strict) {
            return $real;
        }

        return get_absolute_path($path) == $path;
    }
}

if (! function_exists('change_suffix')) {
    /**
     * 修改文件路径的后缀名
     *
     * @param string $filePath
     * @param string $suffix 后缀名，不含"."
     * @return string
     */
    function change_suffix($filePath, $suffix)
    {
        $oldSuffix = strrchr(basename($filePath), '.');

        if (false === $oldSuffix) {
            return "{$filePath}.{$suffix}";
        } else {
            return str_replace_last($oldSuffix, ".{$suffix}", $filePath);
        }
    }
}

if (! function_exists('random_filename')) {
    /**
     * 获取随机生成的文件名（10位随机数）
     *
     * @param mixed $param 文件扩展名，如果参数为`\Illuminate\Http\UploadedFile`对象，通过getExtension获取
     * @return string
     */
    function random_filename($param = null)
    {
        if ($param instanceof \Illuminate\Http\UploadedFile) {
            $extension = $param->extension();
        } else {
            $extension = (string) $param;
        }

        $name = str_random(10);
        return $extension ? "$name.$extension" : $name;
    }
}

if (! function_exists('ensure_dir_exists')) {
    /**
     * 查看目录是否存在，不存在则创建
     *
     * @param string $dir
     * @return bool
     */
    function ensure_dir_exists($dir)
    {
        return is_dir($dir) || mkdir($dir, 0755, true);
    }
}

if (! function_exists('hash_to_path')) {
    /**
     * 把 key 转换成 md5 值，再转成 3 级路径
     *
     * @param string $key
     * @return string
     */
    function hash_to_path($key)
    {
        $hash = md5($key);
        $parts = array_slice(str_split($hash, 2), 0, 2);

        return implode('/', $parts) . '/' . $hash;
    }
}

if (! function_exists('upload_path')) {
    /**
     * 根据时间划分文件存放的相对路径
     *
     * @param string $type 名称单词
     * @return string
     */
    function upload_path($type)
    {
        return str_plural($type) . '/' . date('Ym') . '/' . date('d');
    }
}

if (! function_exists('upload_image')) {
    /**
     * 上传图片
     *
     * @param string $key
     * @param string $type 图片类型，不同类型存放于不同的目录，默认为 image
     * @param int $height 以 height 为最大高度，等比例缩小图片；为0时，不缩放
     * @return false|string
     */
    function upload_image($key, $type = 'image', $height = 0)
    {
        $request = request();

        $file = $request->file($key);
        $path = $file->storeAs(upload_path($type), random_filename($file), ['disk' => 'public']);

        // 等比例缩小
        if ($height > 0) {
            $filePath = Storage::disk('public')->path($path);
            image_shrink($filePath, $height);
        }

        return $path;
    }
}

if (! function_exists('image_shrink')) {
    /**
     * 以 height 为最大高度，等比例缩小图片
     *
     * @param string $filePath 图片路径
     * @param int $height 默认最大高度为 200
     */
    function image_shrink($filePath, $height = 200)
    {
        // 等比例缩小
        Image::make($filePath)->heighten($height, function ($constraint) {
            $constraint->upsize();
        })->save();
    }
}

if (! function_exists('is_image')) {
    function is_image($file)
    {
        $file = new File($file);
        return in_array($file->guessExtension(), ['jpeg', 'png', 'gif', 'bmp', 'svg']);
    }
}

if (! function_exists('image_url')) {
    function image_url($path)
    {
        if (empty($path) || 0 === strpos($path, 'http')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('is_url')) {
    /**
     * 判断是否为链接
     *
     * @param string $value
     * @return bool
     */
    function is_url($value)
    {
        return boolval(filter_var($value, FILTER_VALIDATE_URL));
    }
}

if (! function_exists('change_filename')) {
    /**
     * 修改路径中的文件名
     *
     * @param string $path
     * @return string
     */
    function change_filename($path)
    {
        $parts = pathinfo($path);
        $filename = str_random(10);
        return $parts['dirname'] . "/{$filename}" . ($parts['extension'] ? ".{$parts['extension']}" : '');
    }
}

if (! function_exists('divide')) {
    /**
     * 两个数相除，如果除数为0，返回0
     *
     * @param float|int $dividend 被除数
     * @param float|int $divisor 除数
     * @return float|int
     */
    function divide($dividend, $divisor)
    {
        return 0 == $divisor ? 0 : ($dividend / $divisor);
    }
}

if (! function_exists('array_only_merge')) {
    /**
     * 合并一个或多个数组，但只合并在`array1`中存在的键名。
     * 如果`array1`是数字索引，以值作为键名，再合并。
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    function array_only_merge($array1, $array2)
    {
        $num = func_num_args();
        $args = func_get_args();
        switch ($num) {
            case 0 :
                return [];
            case 1 :
                return $array1;
            case 2 :
                if (Arr::isAssoc($array1)) {
                    $keys = array_keys($array1);
                } else {
                    $keys = $array1;
                    $array1 = array_fill_keys($keys, null);
                }
                return array_merge($array1, array_only($array2, $keys));
            default :
                $array1 = array_shift($args);
                $array2 = call_user_func_array('array_merge', $args);
                return array_only_merge($array1, $array2);
        }
    }
}

if (! function_exists('array_remove')) {
    /**
     * 删除数组中的某个值
     *
     * 如果 needle 在 haystack 中出现不止一次，则删除第一个匹配到的值，并返回对应的 key
     * 如果没匹配到，则返回 false
     *
     * @param array $haystack
     * @param mixed $needle
     * @return mixed
     */
    function array_remove(&$haystack, $needle)
    {
        $key = array_search($needle, $haystack);

        if (false !== $key) {
            unset($haystack[$key]);

            return $key;
        }

        return false;
    }
}

if (! function_exists('array_bind')) {
    /**
     * 根据两个数组的主外键关系，进行绑定
     *
     * @param array $array1
     * @param array $array2
     * @param string $key1 外键
     * @param string $key2 主键
     * @param string $relation
     * @return array
     */
    function array_bind($array1, $relation, $array2, $key1 = null, $key2 = 'id')
    {
        if (null === $key1) {
            $key1 = $relation;
        }

        $plucked = array_column($array2, null, $key2);

        foreach ($array1 as &$item) {
            $item[$relation] = $plucked[$item[$key1]] ?? null;
        }

        return $array1;
    }
}

if (! function_exists('cache_many')) {
    /**
     * 查询多个主键值对应的数据，先从缓存中获取，找不到的话则获取数据再缓存，最后以主键值为 key 返回数据
     *
     * @param array $keyMap 主键值和缓存key的映射
     * @param int $minutes 缓存分钟数
     * @param callable $callable 根据主键值获取数据的回调，返回值需以主键值为 key
     * @return array
     */
    function cache_many($keyMap, $minutes, callable $callable)
    {
        // 找出缓存的数据
        $cacheResults = Cache::many(array_values($keyMap));

        // 找出没有被缓存的主键
        $results = [];
        $noCacheIds = [];
        foreach ($keyMap as $id => $cacheKey) {
            if (is_null($cacheResults[$cacheKey])) {
                $noCacheIds[] = $id;
            } else {
                $results[$id] = $cacheResults[$cacheKey];
            }
        }

        if (empty($noCacheIds)) {
            return $results;
        }

        // 查找未缓存的数据，并进行缓存
        $items = call_user_func($callable, $noCacheIds);

        $caches = [];
        foreach ($items as $id => $item) {
            $caches[$keyMap[$id]] = $item;

            $results[$id] = $item;
        }

        Cache::putMany($caches, $minutes);

        return $results;
    }
}

if (! function_exists('object_only')) {
    /**
     * 获取对象相应的属性，类似 array_only
     *
     * @param stdClass $object
     * @param array $keys
     * @return array
     */
    function object_only($object, $keys)
    {
        $data = [];

        foreach ($keys as $key) {
            $data[$key] = $object->{$key};
        }

        return $data;
    }
}

if (! function_exists('invalidation')) {
    /**
     * 返回数据验证不通过的响应信息
     *
     * @param string $message
     * @param bool $returnException 是否返回异常，默认为 false
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Validation\ValidationException
     */
    function invalidation($message, $returnException = false)
    {
        if ($returnException) {
            return ValidationException::withMessages([$message]);
        }

        return response()->json(['code' => 422, 'message' => $message]);
    }
}

/**
 * 返回数据找不到的响应
 *
 * @param string $model
 * @param int|array $ids
 * @param bool $returnException 是否返回异常，默认为 false
 * @return \Illuminate\Http\JsonResponse|\Illuminate\Database\Eloquent\ModelNotFoundException
 */
function not_found($model, $ids, $returnException = false)
{
    $e = new ModelNotFoundException();
    $e->setModel($model, $ids);

    if ($returnException) {
        return $e;
    }

    return response()->json(['code' => 404, 'message' => $e->getMessage()]);
}

if (! function_exists('access_denied')) {
    /**
     * 无访问权限时，返回的响应信息
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    function access_denied($message = '无访问权限')
    {
        return response()->json(['code' => 403, 'message' => $message]);
    }
}

if (! function_exists('per_page')) {
    /**
     * 获取单页查询个数，并限制最大值
     *
     * @param int $max
     * @return int
     */
    function per_page($max = 50)
    {
        $perPage = intval(request('per_page'));
        if ($perPage <= 0) {
            return 10;
        }

        return min($perPage, $max);
    }
}

if (! function_exists('page_num')) {
    /**
     * 页码
     *
     * @return int
     */
    function page_num()
    {
        return max(1, intval(request('page')));
    }
}

if (! function_exists('get_server_ip')) {
    /**
     * 获取本地 IP
     *
     * @return string
     */
    function get_server_ip()
    {
        return gethostbyname(gethostname());
    }
}

if (! function_exists('signature')) {
    /**
     * 对字符串进行签名
     *
     * @param string $string
     * @return string
     */
    function signature($string)
    {
        $key = config('app.key');

        return hash_hmac('sha256', $string, $key);
    }
}

if (! function_exists('has_valid_signature')) {
    /**
     * 签名校验
     *
     * @param string $signature
     * @param string $string
     * @param int $length 需要比较的长度
     * @return bool
     */
    function has_valid_signature($signature, $string, $length = 0)
    {
        $realSignature = signature($string);

        if ($length) {
            $realSignature = substr($realSignature, 0, $length);
            $signature = substr($signature, 0, $length);
        }

        return hash_equals($realSignature, $signature);
    }
}

if (! function_exists('hashids_encode')) {
    /**
     * 加密整数
     *
     * @param int|array $number
     * @param string $connection
     * @return string
     */
    function hashids_encode($number, $connection = null)
    {
        return Hashids::connection($connection)->encode($number);
    }
}

if (! function_exists('hashids_decode')) {
    /**
     * 解密整数
     *
     * @param string $str
     * @param bool $all
     * @param string $connection
     * @return int|array|null
     */
    function hashids_decode($str, $all = false, $connection = null)
    {
        $numbers = Hashids::connection($connection)->decode($str);

        if ($all) {
            return $numbers;
        }

        return $numbers[0] ?? null;
    }
}

if (! function_exists('app_client')) {
    /**
     * 检查发起请求的客户端
     *
     * @param array|string $name 客户端名称：android, ios, mini_program, web
     * @return bool|string $name 为空时，返回客户端名称
     */
    function app_client($name = '')
    {
        $client = strtolower(request()->header('X-App-Client'));

        if (empty($name)) {
            return in_array($client, ['android', 'ios', 'mini_program', 'web']) ? $client : 'web';
        }

        return in_array($client, (array) $name);
    }
}

if (! function_exists('app_version')) {
    /**
     * 获取客户端应用的产品版本号
     */
    function app_version()
    {
        return request()->header('X-App-Version');
    }
}

if (! function_exists('app_version_compare')) {
    /**
     * 和客户端应用的版本号进行比较
     *
     * @param string $operator
     * @param string $version
     * @return mixed
     */
    function app_version_compare($operator, $version)
    {
        $appVersion = app_version();

        return version_compare($appVersion, $version, $operator);
    }
}
