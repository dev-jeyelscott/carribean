<?php

namespace Database\Seeders;

use App\Models\ContactInquiry;
use Illuminate\Database\Seeder;

class ContactInquirySeeder extends Seeder
{
    /**
     * Seed realistic development messages for the active contact workflow.
     *
     * The addresses and phone numbers are reserved example data and must not be
     * treated as real customer contact information.
     */
    public function run(): void
    {
        foreach ($this->inquiries() as $inquiry) {
            ContactInquiry::query()->updateOrCreate(
                [
                    'email' => $inquiry['email'],
                    'subject' => $inquiry['subject'],
                ],
                $inquiry,
            );
        }
    }

    /**
     * Return representative customer inquiries for admin review and testing.
     *
     * @return list<array{
     *     customer_name: string,
     *     email: string,
     *     phone: string,
     *     subject: string,
     *     message: string,
     *     is_read: bool
     * }>
     */
    private function inquiries(): array
    {
        return [
            [
                'customer_name' => 'Sophia Williams',
                'email' => 'sophia@example.com',
                'phone' => '+1 (310) 555-0101',
                'subject' => 'Online order question',
                'message' => 'I am placing a pickup order for Friday evening. About how long after checkout should I expect the ready-for-pickup email?',
                'is_read' => false,
            ],
            [
                'customer_name' => 'Liam Carter',
                'email' => 'liam@example.com',
                'phone' => '+1 (310) 555-0102',
                'subject' => 'Menu question',
                'message' => 'Does the Ital coconut curry contain gluten, and can it be prepared without the toasted garnish?',
                'is_read' => true,
            ],
            [
                'customer_name' => 'Maya Thompson',
                'email' => 'maya@example.com',
                'phone' => '+1 (424) 555-0103',
                'subject' => 'Delivery to Marina del Rey',
                'message' => 'My ZIP code is 90292. Can you confirm that delivery is available to the marina, including apartment buildings with a front desk?',
                'is_read' => false,
            ],
            [
                'customer_name' => 'Daniel Ruiz',
                'email' => 'daniel@example.com',
                'phone' => '+1 (310) 555-0104',
                'subject' => 'Dinner for eight',
                'message' => 'We are planning dinner for eight next Sunday around 6:30 PM. Is a reservation request the best way to check availability for a group that size?',
                'is_read' => true,
            ],
        ];
    }
}
