<?php

declare(strict_types=1);

namespace App\Http\Controllers\Requests\v1\Tasks;

use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:4|max:250',
            'priority' => 'required|integer|min:1|max:5',
            'description' => 'string|max:2000',
            'status' => ['string', Rule::enum(Status::class)],
            'parent_id' => 'nullable|integer|exists:tasks,id',
        ];
    }
}
