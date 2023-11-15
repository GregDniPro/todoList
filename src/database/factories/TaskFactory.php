<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Status;
use DB;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = $this->getRandomUserId();
        $status = $this->getRandomStatus();

        return [
            'user_id' => $userId,
            'title' => fake()->text('250'),
            'description' => fake()->text('2000'),
            'status' => $status,
            'priority' => fake()->numberBetween(1, 5),
            'completed_at' => ($status == Status::DONE->value) ? fake()->dateTime : null,
            'parent_id' => $this->getRandomParentId($userId),
            'created_at' => fake()->dateTime,
            'updated_at' => fake()->dateTime,
        ];
    }

    protected function getRandomStatus(): string
    {
        $statuses = Status::cases();
        return $statuses[array_rand($statuses)]->value;
    }

    protected function getRandomUserId(): int
    {
        return DB::table('users')->inRandomOrder()->limit(1)->value('id');
    }

    protected function getRandomParentId(int $userId): null|int
    {
        return DB::table('tasks')->where(['user_id' => $userId])
            ->inRandomOrder()->limit(1)->value('id');
    }
}
