<?php

declare(strict_types=1);

namespace App\Http\Controllers\Requests\v1\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'string|min:4|max:250',
            'priority' => 'integer|min:1|max:5',
            'description' => 'string|max:2000',
            'parent_id' => 'nullable|integer|exists:tasks,id',
        ];
    }
}
