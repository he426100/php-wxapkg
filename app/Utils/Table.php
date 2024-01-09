<?php

declare(strict_types=1);

namespace App\Utils;

use PhpTui\Term\Event;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\KeyCode;
use PhpTui\Tui\Color\AnsiColor;
use PhpTui\Tui\Example\Demo\Component;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\Table\TableCell;
use PhpTui\Tui\Extension\Core\Widget\Table\TableRow;
use PhpTui\Tui\Extension\Core\Widget\Table\TableState;
use PhpTui\Tui\Extension\Core\Widget\TableWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Text\Span;
use PhpTui\Tui\Text\Title;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\Widget;

final class Table
{
    /** @var WxidInfo[] */
    private array $wxidInfo;
    private int $selected = 0;
    private TableState $state;

    public function __construct()
    {
        $this->state = new TableState();
    }

    public function setData(array $wxidInfo)
    {
        if (!isset($wxidInfo[$this->selected])) {
            $this->selected = 0;
        }
        $this->wxidInfo = $wxidInfo;
    }

    public function build(): Widget
    {
        return BlockWidget::default()->titles(Title::fromString('Table'))->borders(Borders::ALL)
            ->widget(
                TableWidget::default()
                    ->state($this->state)
                    ->select($this->selected)
                    ->highlightStyle(Style::default()->black()->onCyan())
                    ->widths(
                        Constraint::percentage(10),
                        Constraint::min(30),
                        Constraint::min(30),
                        Constraint::min(50),
                    )
                    ->header(
                        TableRow::fromCells(
                            TableCell::fromString('Appid'),
                            TableCell::fromString('Name'),
                            TableCell::fromString('Developer'),
                            TableCell::fromString('Description'),
                        )
                    )
                    ->rows(...array_map(function (WxidInfo $info) {
                        $desc = $info->error ?: $info->description;
                        return TableRow::fromCells(
                            TableCell::fromString($info->appid ?: $info->wxid,),
                            TableCell::fromString($info->nickname),
                            TableCell::fromString($info->principal_name),
                            TableCell::fromString(mb_substr($desc, 0, 30) . (mb_strlen($desc) > 30 ? '...' : '')),
                        );
                    }, $this->wxidInfo))
            )
        ;
    }

    public function handle(Event $event): void
    {
        if ($event instanceof CodedKeyEvent) {
            if ($event->code === KeyCode::Down) {
                $this->selected++;
            }
            if ($event->code === KeyCode::Up) {
                if ($this->selected > 0) {
                    $this->selected--;
                }
            }
        }
    }
}
