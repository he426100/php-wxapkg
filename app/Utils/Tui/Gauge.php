<?php

declare(strict_types=1);

namespace App\Utils\Tui;

use PhpTui\Term\Event;
use PhpTui\Tui\Color\LinearGradient;
use PhpTui\Tui\Color\RgbColor;
use PhpTui\Tui\Example\Demo\Component;
use PhpTui\Tui\Extension\Core\Widget\Block\Padding;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\GaugeWidget;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Position\FractionalPosition;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Span;
use PhpTui\Tui\Text\Title;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\Widget;

final class Gauge
{
    private ?Download $download = null;

    public function __construct()
    {
        $this->download = new Download(1, 0);
    }

    public function setData(Download $download)
    {
        $this->download = $download;
    }

    public function build(): Widget
    {
        return BlockWidget::default()
            ->borders(Borders::ALL)
            ->padding(Padding::all(2))
            ->widget(
                GridWidget::default()
                    ->direction(Direction::Horizontal)
                    ->constraints(
                        Constraint::percentage(30),
                        Constraint::percentage(70),
                    )
                    ->widgets(
                        ParagraphWidget::fromSpans(
                            Span::fromString(sprintf(
                                ' %d/%s',
                                $this->download->downloaded,
                                $this->download->size,
                            ))->style(
                                Style::default()->white()
                            )
                        ),
                        GaugeWidget::default()
                            ->ratio($this->download->ratio())
                            ->style(
                                Style::default()->fg(
                                    LinearGradient::from(
                                        RgbColor::fromRgb(255, 100, 100)
                                    )->addStop(
                                        0.5,
                                        RgbColor::fromRgb(50, 255, 50)
                                    )->addStop(
                                        1,
                                        RgbColor::fromRgb(0, 255, 255)
                                    )->withDegrees(0)->withOrigin(FractionalPosition::at(0, 0))
                                )
                            )
                    )
            );
    }

    public function handle(Event $event): void
    {
    }
}
