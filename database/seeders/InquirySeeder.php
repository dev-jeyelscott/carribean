<?php

namespace Database\Seeders;

use App\Models\ContactInquiry;
use Illuminate\Database\Seeder;

class InquirySeeder extends Seeder
{
    public function run(): void
    {
        ContactInquiry::updateOrCreate(
            [
                'email' => 'sophia@example.com',
                'subject' => 'Private dinner inquiry',
            ],
            [
                'customer_name' => 'Sophia Williams',
                'phone' => '+1 (555) 401-3001',
                'message' => 'Hello, I would like to ask about hosting a private dinner for 12 guests next month.',
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
                'message' => 'Do you have vegetarian options available for dinner service?',
                'is_read' => true,
            ],
        );
    }
}
