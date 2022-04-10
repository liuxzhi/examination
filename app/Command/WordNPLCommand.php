<?php

namespace app\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;


/**
 * 挑选word中是否含有图片
 */
class WordNPLCommand extends Command
{
    /**
     * 配置参数
     */
    protected function configure()
    {
        $this->setName('')
            ->setDescription('WordNPLCommand')
            ->addArgument("path", InputArgument::REQUIRED, "路径为必填项");
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        return 1;
    }
}
