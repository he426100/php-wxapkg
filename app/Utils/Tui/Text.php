<?php

declare(strict_types=1);

namespace App\Utils\Tui;

use PhpTui\Term\Event;
use PhpTui\Tui\Extension\Core\Widget\Block\Padding;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Span;
use PhpTui\Tui\Text\Text as TText;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\Widget;

final class Text
{
    public function __construct(private string $text = '')
    {
    }

    public function setData(string $text)
    {
        $this->text = $text;
    }

    public function build(): Widget
    {
        return BlockWidget::default()
            ->borders(Borders::ALL)
            ->padding(Padding::all(2))
            ->widget(
                ParagraphWidget::fromText(TText::fromString($this->text)->patchStyle(Style::default()->green())),
            );
    }

    public function handle(Event $event): void
    {
    }
}
