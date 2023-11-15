<?php

declare(strict_types=1);

namespace App\Http\Controllers\Interfaces\v1;

use App\Http\Controllers\Requests\v1\Tasks\CreateTaskRequest;
use App\Http\Controllers\Requests\v1\Tasks\IndexTasksRequest;
use App\Http\Controllers\Requests\v1\Tasks\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;

interface TaskControllerInterface
{
    public function index(IndexTasksRequest $request): TaskResource;

    public function show(Task $task): TaskResource;

    public function store(CreateTaskRequest $request): TaskResource;

    public function update(Task $task, UpdateTaskRequest $request): TaskResource;

    public function done(Task $task): TaskResource;

    public function destroy(Task $task): JsonResponse;
}
