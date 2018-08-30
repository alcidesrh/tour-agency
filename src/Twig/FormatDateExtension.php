<?php


namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 *
 * @author Alcides Rodriguez <weaverryan@gmail.com>
 */
class FormatDateExtension extends AbstractExtension
{

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_date', [$this, 'formatDate']),
            new TwigFilter('format_time', [$this, 'formatTime']),
        ];
    }

    public function formatDate(\DateTime $date): string
    {
        return $date->format( 'd-m-Y' );
    }

    public function formatTime(\DateTime $date): string
    {
        return $date->format( 'H:m a' );
    }
}
