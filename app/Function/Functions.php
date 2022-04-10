<?php


if (!function_exists('readPathFiles')) {
    /**
     * 取出某目录下所有php文件的文件名.
     *
     * @param string $path 文件夹目录
     *
     * @return array 文件名
     */
    function readPathFiles(string $path, string $suffix = "docx"): array
    {
        $data = [];
        if (!is_dir($path)) {
            return $data;
        }
        $files = scandir($path);
        foreach ($files as $file) {
            if (is_dir($path . $file)) {
                continue;
            }
            if (in_array($file, ['.', '..', '.DS_Store'])) {
                continue;
            }
            $data[] = $file;
            //$data[] = $path . preg_replace('/(\w+)\.' . $suffix . '/', '$1', $file);
        }
        return $data;
    }
}