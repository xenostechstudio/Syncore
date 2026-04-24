<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\JournalEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    public function definition(): array
    {
        return [
            'entry_number' => 'JE-' . fake()->unique()->numerify('######'),
            'entry_date' => now()->toDateString(),
            'reference' => fake()->optional()->bothify('REF-####'),
            'description' => fake()->optional()->sentence(),
            'total_debit' => 0,
            'total_credit' => 0,
            'status' => 'draft',
        ];
    }

    public function posted(): static
    {
        return $this->state(fn () => ['status' => 'posted']);
    }
}
