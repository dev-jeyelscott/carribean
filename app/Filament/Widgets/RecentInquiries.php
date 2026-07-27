<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Models\ContactInquiry;
use Carbon\CarbonInterface;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Widgets\Widget;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @phpstan-type RecentInquiry array{
 *     type: 'Contact Inquiry',
 *     customer_name: string,
 *     summary: string,
 *     is_read: bool,
 *     created_at: CarbonInterface,
 *     url: string
 * }
 */
class RecentInquiries extends Widget
{
    private const DISPLAY_LIMIT = 8;

    protected static ?int $sort = 2;

    protected string $view = 'filament.widgets.recent-inquiries';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'lg' => 8,
    ];

    public static function canView(): bool
    {
        if (! self::canAccessAdminPanel()) {
            return false;
        }

        return ContactInquiryResource::canViewAny();
    }

    /**
     * @return array{
     *     inquiries: Collection<int, RecentInquiry>,
     *     loadError: bool
     * }
     */
    protected function getViewData(): array
    {
        try {
            $inquiries = collect()
                ->concat($this->contactInquiries())
                ->sortByDesc(
                    fn (array $inquiry): int => $inquiry['created_at']->getTimestamp(),
                )
                ->take(self::DISPLAY_LIMIT)
                ->values();

            return [
                'inquiries' => $inquiries,
                'loadError' => false,
            ];
        } catch (QueryException $exception) {
            report($exception);

            return [
                'inquiries' => collect(),
                'loadError' => true,
            ];
        }
    }

    /**
     * @return Collection<int, RecentInquiry>
     */
    private function contactInquiries(): Collection
    {
        if (! ContactInquiryResource::canViewAny()) {
            return collect();
        }

        return ContactInquiry::query()
            ->select([
                'id',
                'customer_name',
                'subject',
                'is_read',
                'created_at',
            ])
            ->latestFirst()
            ->limit(self::DISPLAY_LIMIT)
            ->get()
            ->map(fn (ContactInquiry $inquiry): array => $this->makeRecentInquiry(
                type: 'Contact Inquiry',
                customerName: $inquiry->customer_name,
                summary: filled($inquiry->subject)
                    ? Str::limit($inquiry->subject, 60)
                    : 'General inquiry',
                isRead: $inquiry->is_read,
                createdAt: $inquiry->created_at ?? now(),
                url: ContactInquiryResource::getUrl('view', [
                    'record' => $inquiry,
                ]),
            ));
    }

    /**
     * @param  'Contact Inquiry'  $type
     * @return RecentInquiry
     */
    private function makeRecentInquiry(
        string $type,
        string $customerName,
        string $summary,
        bool $isRead,
        CarbonInterface $createdAt,
        string $url,
    ): array {
        return [
            'type' => $type,
            'customer_name' => $customerName,
            'summary' => $summary,
            'is_read' => $isRead,
            'created_at' => $createdAt,
            'url' => $url,
        ];
    }

    private static function canAccessAdminPanel(): bool
    {
        $user = auth()->user();

        return $user instanceof FilamentUser
            && $user->canAccessPanel(Filament::getPanel('admin'));
    }
}
