<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserInfo>
 */
class UserInfoFactory extends Factory
{
    protected $model = UserInfo::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'middle_name' => $this->faker->firstName,
            'birthday' => $this->faker->date,
            'phone' => $this->faker->phoneNumber,
            'user_id' => User::newFactory(),
        ];
    }
}
