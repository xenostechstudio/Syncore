<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company_name' => fake()->company(),
            'job_title' => fake()->jobTitle(),
            'source' => fake()->randomElement(['website', 'referral', 'social', 'event', 'other']),
            'status' => 'new',
            'notes' => fake()->optional()->sentence(),
            'assigned_to' => null,
        ];
    }

    public function converted(): static
    {
        return $this->state(fn () => ['status' => 'converted']);
    }

    public function qualified(): static
    {
        return $this->state(fn () => ['status' => 'qualified']);
    }
}
