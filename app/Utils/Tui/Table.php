<?php

declare(strict_types=1);

namespace App\Utils\Tui;

use App\Utils\WxidInfo;
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

    public function key(): int
    {
        return $this->selected;
    }

    public function current(): ?WxidInfo
    {
        return $this->wxidInfo[$this->selected] ?? null;
    }

    public function count(): int
    {
        return count($this->wxidInfo);
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
                        Constraint::min(35),
                        Constraint::min(35),
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
                        return TableRow::fromCells(
                            TableCell::fromString($info->appid ?: $info->wxid,),
                            TableCell::fromString(text_ellipsis($info->nickname, 15)),
                            TableCell::fromString(text_ellipsis($info->principal_name, 15)),
                            TableCell::fromString(text_ellipsis($info->error ?: $info->description, 40)),
                        );
                    }, $this->wxidInfo))
            );
    }

    public function handle(Event $event): void
    {
        if ($event instanceof CodedKeyEvent) {
            $this->selected = match ($event->code) {
                KeyCode::Down => min($this->selected + 1, count($this->wxidInfo) - 1),
                KeyCode::Up => max(0, $this->selected - 1),
                KeyCode::Home => 0,
                KeyCode::End => count($this->wxidInfo) - 1,
                KeyCode::PageUp => max(0, $this->selected - 10),
                KeyCode::PageDown => min($this->selected + 10, count($this->wxidInfo) - 1),
                default => $this->selected,
            };
        }
    }
}
