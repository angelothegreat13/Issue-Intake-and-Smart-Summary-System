<?php

namespace Database\Factories;

use App\Enums\Category;
use App\Enums\Priority;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class IssueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'            => $this->faker->sentence(6),
            'description'      => $this->faker->paragraph(3),
            'priority'         => $this->faker->randomElement(Priority::cases())->value,
            'category'         => $this->faker->randomElement(Category::cases())->value,
            'status'           => $this->faker->randomElement(Status::cases())->value,
            'summary'          => $this->faker->sentence(),
            'suggested_action' => $this->faker->sentence(),
            'escalated'        => false,
            'due_at'           => null,
        ];
    }
}
