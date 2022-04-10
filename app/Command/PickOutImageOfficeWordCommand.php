<?php

namespace app\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Image;
use throwable;

/**
 * 挑选word中是否含有图片
 */
class PickOutImageOfficeWordCommand extends Command
{
    /**
     * 配置参数
     */
    protected function configure()
    {
        $this->setName('PickOutImageOfficeWordCommand')
            ->setDescription('PickOutImageOfficeWordCommand')
            ->addArgument("path", InputArgument::REQUIRED, "路径为必填项");
    }

    /**
     *
     * @param $source
     * @return bool
     */
    protected function pickOutImageOfficeWord($source)
    {
        $fileName = basename($source);
        $dirName = dirname($source);
        $name = pathinfo($source, PATHINFO_FILENAME);


        try {
            $sections = IOFactory::load($source)
                ->getSections();
        } catch (throwable $throwable) {
            $errorPath = $dirName . "/error_doc/";
            if (!is_dir($errorPath)) {
                mkdir($errorPath, 0777);
            }

            copy($dirName.$fileName, $errorPath . $fileName);

            return false;
        }

        $image = false;
        foreach ($sections as $section) {
            $elements = $section->getElements();
            foreach ($elements as $element) {
                if ($element instanceof TextRun) {
                    $contentElements = $element->getElements();
                    foreach ($contentElements as $contentElement) {
                        if ($contentElement instanceof Image) {
                            $image = true;
                            $imageDataTmp = $contentElement->getImageStringData(true);
                            $imageType = 'image/jpg';
                            if ($contentElement->getImageType()) {
                                $imageType = $contentElement->getImageType();
                            }
                            $imageData = 'data:' . $imageType . ';base64,' . str_replace(["\r\n", "\r", "\n"], "",
                                    $imageDataTmp);
                            $wordImageBasePath = $dirName . "/images/";
                            if (!is_dir($wordImageBasePath)) {
                                mkdir($wordImageBasePath, 0777);
                            }

                            $wordImagePath = $dirName . "/images/" . $name . "/";
                            if (!is_dir($wordImagePath)) {
                                mkdir($wordImagePath, 0777);
                            }
                            $imageSrc = $wordImagePath . md5($contentElement->getSource()) . '.' . $contentElement->getImageExtension();
                            if (!is_file($imageSrc)) {
                                file_put_contents($imageSrc, base64_decode(explode(',', $imageData)[1]));
                            }
                        }
                    }
                } elseif ($element instanceof Image) {
                    $image = true;
                    $imageDataTmp = $element->getImageStringData(true);
                    $imageType = 'image/jpg';
                    if ($element->getImageType()) {
                        $imageType = $element->getImageType();
                    }
                    $imageData = 'data:' . $imageType . ';base64,' . str_replace(["\r\n", "\r", "\n"], "",
                            $imageDataTmp);
                    $wordImageBasePath = $dirName . "/images/";
                    if (!is_dir($wordImageBasePath)) {
                        mkdir($wordImageBasePath, 0777);
                    }

                    $wordImagePath = $dirName . "/images/" . $name . "/";
                    if (!is_dir($wordImagePath)) {
                        mkdir($wordImagePath, 0777);
                    }
                    $imageSrc = $wordImagePath . md5($element->getSource()) . '.' . $element->getImageExtension();
                    if (!is_file($imageSrc)) {
                        file_put_contents($imageSrc, base64_decode(explode(',', $imageData)[1]));
                    }
                }

            }
        }


        $basePath = $dirName . "/no_image_doc/";
        $imagePath = $dirName . "/image_doc/";

        if (!is_dir($imagePath)) {
            mkdir($imagePath, 0777);
        }

        if (!is_dir($basePath)) {
            mkdir($basePath, 0777);
        }

        if ($image) {
            copy($source, $imagePath . $fileName);
        } else {
            copy($source, $basePath . $fileName);
        }
        return $image;
    }


    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');
        $output->writeln("输入路径：" . $path);
        $files = readPathFiles($path);
        foreach ($files as $file) {
            $output->writeln("判断" . basename($file) . " 是否存在图片?");
            $result = $this->pickOutImageOfficeWord($file);
            if ($result) {
                $output->writeln(basename($file) . ":包含在图片");
            } else {
                $output->writeln(basename($file) . ":不包含在图片");
            }
        }
        return 1;
    }
}
