<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class ScanTUI
{
    /** @var WxidInfo[] */
    private array $wxidInfo;

    public function __construct(array $wxidInfo)
    {
        $this->wxidInfo = $wxidInfo;
    }

    public function render(): void
    {
        $table = new Table(new ConsoleOutput());
        $table->setHeaders([
            ['<fg=magenta>Appid</>', '<fg=magenta>Name</>', '<fg=magenta>Developer</>', '<fg=magenta>Description</>'],
        ]);

        foreach ($this->wxidInfo as $info) {
            $table->addRow([
                $info->appid ?: $info->wxid,
                $info->nickname,
                $info->principal_name,
                $info->error ?: $info->description,
            ]);
        }

        $table->getStyle()->setBorderFormat('%s');
        $table->setColumnMaxWidth(0, 20);
        $table->setColumnMaxWidth(1, 30);
        $table->setColumnMaxWidth(2, 40);

        $table->render();
    }
}
