<?php

namespace App\Support\Dashboard;

use App\Enums\FulfillmentMethod;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Throwable;

final readonly class DashboardPeriod
{
    /**
     * Store normalized reporting boundaries and the optional fulfillment
     * filter shared by every dashboard widget.
     */
    public function __construct(
        public CarbonImmutable $start,
        public CarbonImmutable $end,
        public ?FulfillmentMethod $fulfillmentMethod,
    ) {}

    /**
     * Normalize raw Filament dashboard-filter values.
     *
     * The default period is the trailing seven calendar days, including
     * today, in the configured restaurant timezone.
     *
     * @param  array<string, mixed>  $filters
     */
    public static function fromFilters(array $filters): self
    {
        $timezone = (string) config(
            'app.timezone',
            'UTC',
        );

        $today = CarbonImmutable::now($timezone)
            ->startOfDay();

        $start = self::parseDate(
            $filters['startDate'] ?? null,
            $timezone,
        )?->startOfDay() ?? $today->subDays(6);

        $end = self::parseDate(
            $filters['endDate'] ?? null,
            $timezone,
        )?->endOfDay() ?? $today->endOfDay();

        if ($start->greaterThan($end)) {
            $originalStart = $start;

            $start = $end->startOfDay();
            $end = $originalStart->endOfDay();
        }

        $fulfillmentValue =
            $filters['fulfillmentMethod'] ?? null;

        $fulfillmentMethod = is_string(
            $fulfillmentValue,
        )
            ? FulfillmentMethod::tryFrom(
                $fulfillmentValue,
            )
            : null;

        return new self(
            start: $start,
            end: $end,
            fulfillmentMethod: $fulfillmentMethod,
        );
    }

    /**
     * Build the immediately preceding period with the same number of days.
     */
    public function previous(): self
    {
        $previousEnd = $this->start->subSecond();

        $previousStart = $previousEnd
            ->startOfDay()
            ->subDays($this->numberOfDays() - 1);

        return new self(
            start: $previousStart,
            end: $previousEnd,
            fulfillmentMethod: $this->fulfillmentMethod,
        );
    }

    /**
     * Return the inclusive number of calendar days in this period.
     */
    public function numberOfDays(): int
    {
        return (int) $this->start
            ->startOfDay()
            ->diffInDays(
                $this->end->startOfDay(),
            ) + 1;
    }

    /**
     * Determine whether the selected period contains only today.
     */
    public function isToday(): bool
    {
        return $this->numberOfDays() === 1
            && $this->start->isToday();
    }

    /**
     * Return the database date keys represented by this period.
     *
     * @return list<string>
     */
    public function dateKeys(): array
    {
        $keys = [];

        for (
            $date = $this->start->startOfDay();
            $date->lessThanOrEqualTo($this->end);
            $date = $date->addDay()
        ) {
            $keys[] = $date->format('Y-m-d');
        }

        return $keys;
    }

    /**
     * Return compact labels suitable for the revenue chart.
     *
     * @return list<string>
     */
    public function chartLabels(): array
    {
        $labels = [];

        $format = $this->numberOfDays() <= 14
            ? 'D'
            : 'M j';

        for (
            $date = $this->start->startOfDay();
            $date->lessThanOrEqualTo($this->end);
            $date = $date->addDay()
        ) {
            $labels[] = $date->format($format);
        }

        return $labels;
    }

    /**
     * Return a human-readable date-range label.
     */
    public function label(): string
    {
        if ($this->numberOfDays() === 1) {
            return $this->start->format('M j, Y');
        }

        if ($this->start->year === $this->end->year) {
            return sprintf(
                '%s – %s',
                $this->start->format('M j'),
                $this->end->format('M j, Y'),
            );
        }

        return sprintf(
            '%s – %s',
            $this->start->format('M j, Y'),
            $this->end->format('M j, Y'),
        );
    }

    /**
     * Safely parse one optional date-filter value.
     */
    private static function parseDate(
        mixed $value,
        string $timezone,
    ): ?CarbonImmutable {
        if ($value instanceof CarbonInterface) {
            return CarbonImmutable::instance($value)
                ->setTimezone($timezone);
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse(
                $value,
                $timezone,
            );
        } catch (Throwable) {
            return null;
        }
    }
}
