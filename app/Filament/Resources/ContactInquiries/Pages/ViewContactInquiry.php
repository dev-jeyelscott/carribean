<?php

namespace App\Filament\Resources\ContactInquiries\Pages;

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Models\ContactInquiry;
use Filament\Resources\Pages\ViewRecord;

class ViewContactInquiry extends ViewRecord
{
    protected static string $resource = ContactInquiryResource::class;

    /**
     * Load the inquiry and record that an administrator reviewed it.
     */
    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var ContactInquiry $contactInquiry */
        $contactInquiry = $this->getRecord();

        if ($contactInquiry->is_read) {
            return;
        }

        $contactInquiry->update([
            'is_read' => true,
        ]);
    }

    /**
     * Return the page-level actions available for an inquiry.
     *
     * @return array<int, mixed>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
