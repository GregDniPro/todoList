<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Http\Controllers\Requests\v1\Tasks\CreateTaskRequest;
use App\Http\Controllers\Requests\v1\Tasks\IndexTasksRequest;
use App\Http\Controllers\Requests\v1\Tasks\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

interface TasksRepositoryInterface
{
    public const ITEMS_PER_PAGE = 50;

    public function getUserTasks(IndexTasksRequest $request): LengthAwarePaginator;

    public function createTask(CreateTaskRequest $request): Task;

    public function updateTask(Task $task, UpdateTaskRequest $request): Task;

    public function completeUserTask(Task $task): Task;
}
