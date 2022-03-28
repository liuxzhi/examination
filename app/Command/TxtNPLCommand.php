<?php

namespace app\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use App\Model\question;

/**
 * 挑选word中是否含有图片
 */
class TxtNPLCommand extends Command
{
    /**
     * 配置参数
     */
    protected function configure()
    {
        $this->setName('TxtNPLCommand')
            ->setDescription('TxtNPLCommand')
            ->addArgument("path", InputArgument::REQUIRED, "路径为必填项");
    }

    /**
     * 获取txt分割符号
     * @param $filePath
     * @return string
     */
    protected function getSegmentation($filePath): string
    {
        $file = file_get_contents($filePath, "r");
        $segmentation = "\.";
        if (preg_match("/\d{1,4}$segmentation/", $file, $questionsMatches)) {
            return $segmentation;
        }

        $widthSegmentation = "\．";
        if (preg_match("/\d{1,4}$widthSegmentation/", $file, $questionsMatches)) {
            return $widthSegmentation;
        }
    }

    /**
     *
     * @param string $filePath
     * @param string $segmentation
     * @param array $questionTypeList
     *
     * @return array|void
     */
    protected function readNLP(string $filePath = "", string $segmentation, array $questionTypeList): array
    {
        try {

            $file = fopen($filePath, "r");
            if (empty($file)) {
                return [];
            }
            $i = 0;

            $heads = [];
            $questions = [];
            $questionType = [];
            $start = false;
            $content = [];
            // 每次获取一行，进行匹配
            while (!feof($file)) {
                $i++;
                $itemLine = rtrim(fgets($file));
                if (preg_match('/' . implode('|', $questionTypeList) . '/', $itemLine, $questionsTypeMatches)) {
                    $questionType[] = $itemLine;
                    continue;
                }
                // 匹配1.
                if ($start == false) {
                    if (preg_match("/1$segmentation/", $itemLine, $startMatches)) {
                        $start = true;
                    }
                }
                // 试题的头部
                if ($start == false) {
                    $heads[] = $itemLine;
                    continue;
                }
                // 试题的题目匹配 1. 2. ...........
                if (preg_match("/^\d{1,4}$segmentation/", $itemLine, $questionsMatches)) {
                    if (empty($content)) {
                        $content[] = $itemLine;
                    } else {
                        $temp = implode("", $content);
                        $questions[] = $temp;
                        $content = [];
                        $content[] = $itemLine;
                    }
                } else {
                    $content[] = $itemLine;
                }

            }
            fclose($file);

            return $questions;
        } catch (Exception $exception) {
            throw $exception;
        }
    }


    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $questionTypeList = [
            '填空题',
            '选择题',
            '简答题',
            '名词解释',
            '论述题',
            '计算及分录题',
        ];

        $path = $input->getArgument('path');
        $output->writeln("输入路径：" . $path);
        $files = readPathFiles($path, "txt");
        foreach ($files as $file) {
            $output->writeln("导入" . basename($file));
            $segmentation = $this->getSegmentation($file);
            $questions = $this->readNLP($file, $segmentation, $questionTypeList);
            foreach ($questions as $questions) {
                question::create(["content" => $questions]);
            }
        }
        return 1;
    }
}
