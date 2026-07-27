<?php

namespace Database\Seeders;

use App\Models\ContactInquiry;
use Illuminate\Database\Seeder;

class ContactInquirySeeder extends Seeder
{
    /**
     * Seed development-only contact messages that match the active contact flow.
     */
    public function run(): void
    {
        ContactInquiry::updateOrCreate(
            [
                'email' => 'sophia@example.com',
                'subject' => 'Online order question',
            ],
            [
                'customer_name' => 'Sophia Williams',
                'phone' => '+1 (555) 401-3001',
                'message' => 'Hello, I have a question about pickup availability for an online order.',
                'is_read' => false,
            ],
        );

        ContactInquiry::updateOrCreate(
            [
                'email' => 'liam@example.com',
                'subject' => 'Menu question',
            ],
            [
                'customer_name' => 'Liam Carter',
                'phone' => '+1 (555) 401-3002',
                'message' => 'Do you have vegetarian options available on the current menu?',
                'is_read' => true,
            ],
        );
    }
}
