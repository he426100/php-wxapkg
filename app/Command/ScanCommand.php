<?php

declare(strict_types=1);

namespace App\Command;

use App\Utils\Download;
use App\Utils\Gauge;
use App\Utils\Table;
use App\Utils\WxidInfo;
use App\Utils\WxidQuery;
use PhpTui\Term\Actions;
use PhpTui\Term\ClearType;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\KeyCode;
use PhpTui\Term\KeyModifiers;
use PhpTui\Term\Terminal;
use PhpTui\Tui\Bridge\PhpTerm\PhpTermBackend;
use PhpTui\Tui\DisplayBuilder;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Layout\Constraint;
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
            )
            ->addOption(
                'speed',
                't',
                InputOption::VALUE_OPTIONAL,
                'scan speed',
                0
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $root = $input->getOption('root');
        if (empty($root)) {
            $root = path_join(get_home(), 'Documents/WeChat Files/Applet');
        }
        $speed = (int)$input->getOption('speed');

        $regAppId = '/(wx[0-9a-f]{16})/';
        $files = iterator_to_array(new \FilesystemIterator($root), false);
        uasort($files, fn ($a, $b) => $a->getMTime() - $b->getMTime());
        $wxidInfos = [];

        $sleep = $speed > 0 ? fn () => sleep($speed) : fn () => null;

        $terminal = Terminal::new();
        $display = DisplayBuilder::default(PhpTermBackend::new($terminal))->build();

        try {
            // enable "raw" mode to remove default terminal behavior (e.g.
            // echoing key presses)
            // hide the cursor
            $terminal->execute(Actions::cursorHide());
            // switch to the "alternate" screen so that we can return the user where they left off
            $terminal->execute(Actions::alternateScreenEnable());
            $terminal->execute(Actions::enableMouseCapture());
            $terminal->enableRawMode();

            $i = $count = count($files);
            $table = new Table();
            $gauge = new Gauge();

            while (1) {
                $table->setData($wxidInfos);
                $gauge->setData(new Download($count, $count - max($i, 0)));
                while (null !== $event = $terminal->events()->next()) {
                    if ($event instanceof CharKeyEvent) {
                        if ($event->char === 'q') {
                            break 2;
                        }
                    }
                    if ($event instanceof CodedKeyEvent) {
                        if ($event->code === KeyCode::Esc) {
                            // do something
                        }
                    }
                    $table->handle($event);
                }

                $display->draw(GridWidget::default()
                    ->direction(Direction::Vertical)
                    ->constraints(
                        Constraint::min(40),
                        Constraint::min(0),
                    )
                    ->widgets(
                        $table->build(),
                        $gauge->build(),
                    ));

                while ($i-- > -1) {
                    $file = $files[$i];
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
                        $i++;
                        break;
                    }
                    $wxidInfos[] = $info;
                    $sleep();
                }
                // sleep for Xms - note that it's encouraged to implement apps
                // using an async library such as Amp or React
                usleep(50_000);
            }
        } finally {
            $terminal->disableRawMode();
            $terminal->execute(Actions::disableMouseCapture());
            $terminal->execute(Actions::alternateScreenDisable());
            $terminal->execute(Actions::cursorShow());
            $terminal->execute(Actions::clear(ClearType::All));
        }

        return 0;
    }
}
