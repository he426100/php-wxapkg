<?php

declare(strict_types=1);

namespace App\Command;

use App\Utils\ScanTUI;
use App\Utils\WxidInfo;
use App\Utils\WxidQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class ScanCommand extends Command
{
    protected static $defaultName = 'scan';

    protected function configure(): void
    {
        $this
            ->setDescription('Scan the wechat mini program')
            ->setHelp('wxapkg scan -r "/path/to/wechat/files/Applet"')
            ->addOption(
                'root',
                'r',
                InputOption::VALUE_OPTIONAL,
                'The wechat path'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $root = $input->getOption('root');
        if (empty($root)) {
            $root = path_join(get_home(), 'Documents/WeChat Files/Applet');
        }

        $regAppId = '/(wx[0-9a-f]{16})/';
        $files = iterator_to_array(new \FilesystemIterator($root));
        uasort($files, fn($a, $b) => $b->getMTime() - $a->getMTime());
        $wxidInfos = [];

        foreach ($files as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isDir() || !preg_match($regAppId, $file->getFilename())) {
                continue;
            }

            $wxid = preg_replace_callback($regAppId, function ($matches) {
                return $matches[1];
            }, $file->getFilename());

            /** @var WxidInfo */
            $info = WxidQuery::query($wxid);
            $info->location = $file->getPathname();
            $info->wxid = $wxid;
            if ($info->error) {
                break;
            }
            $wxidInfos[] = $info;

            sleep(3);
        }

        $tui = new ScanTUI($wxidInfos);
        $tui->render();

        return 0;
    }
}
