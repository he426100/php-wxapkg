<?php

declare(strict_types=1);

namespace App\Command;

use App\Utils\Tui\Download;
use App\Utils\Tui\Gauge;
use App\Utils\Tui\Table;
use App\Utils\Tui\Text;
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
                'wait',
                't',
                InputOption::VALUE_OPTIONAL,
                'scan wait',
                0
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $root = $input->getOption('root');
        if (empty($root)) {
            $root = path_join(get_home(), 'Documents/WeChat Files/Applet');
        }
        $wait = (int)$input->getOption('wait');

        $regAppId = '/(wx[0-9a-f]{16})/';
        $files = iterator_to_array(new \FilesystemIterator($root), false);
        uasort($files, fn ($a, $b) => $a->getMTime() - $b->getMTime());
        $wxidInfos = [];

        $query = new WxidQuery();
        $terminal = Terminal::new();
        $display = DisplayBuilder::default(PhpTermBackend::new($terminal))->build();

        try {
            // hide the cursor
            // $terminal->execute(Actions::cursorHide());
            // switch to the "alternate" screen so that we can return the user where they left off
            $terminal->execute(Actions::alternateScreenEnable());
            // $terminal->execute(Actions::enableMouseCapture());
            // enable "raw" mode to remove default terminal behavior (e.g.
            // echoing key presses)
            $terminal->enableRawMode();

            $i = $count = count($files);
            $table = new Table();
            $gauge = new Gauge();
            $text = new Text();

            while (1) {
                $table->setData($wxidInfos);
                $gauge->setData(new Download($table->count(), $table->key() + 1));
                $text->setData((string)$table->current());

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
                        Constraint::min(5),
                        Constraint::length(15),
                        Constraint::length(8),
                    )
                    ->widgets(
                        $table->build(),
                        $text->build(),
                        $gauge->build(),
                    ));

                while ($i-- > -1) {
                    $file = $files[$i] ?? null;
                    if (!$file instanceof \SplFileInfo || !$file->isDir() || !preg_match($regAppId, $file->getFilename())) {
                        continue;
                    }

                    $wxid = preg_replace_callback($regAppId, function ($matches) {
                        return $matches[1];
                    }, $file->getFilename());

                    /** @var WxidInfo */
                    $info = $query->query($wxid);
                    $info->location = $file->getPathname();
                    $info->wxid = $wxid;
                    if ($info->error) {
                        $i++;
                        sleep($wait);
                        break;
                    }
                    $wxidInfos[] = $info;
                }
                // sleep for Xms - note that it's encouraged to implement apps
                // using an async library such as Amp or React
                usleep(50_000);
            }
        } finally {
            $terminal->disableRawMode();
            // $terminal->execute(Actions::disableMouseCapture());
            $terminal->execute(Actions::alternateScreenDisable());
            // $terminal->execute(Actions::cursorShow());
            $terminal->execute(Actions::clear(ClearType::All));
        }

        return 0;
    }
}
