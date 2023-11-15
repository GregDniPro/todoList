<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Status;
use App\Exceptions\DatabaseOperationException;
use App\Exceptions\TaskHasUndoneChildrenException;
use App\Http\Controllers\Requests\v1\Tasks\CreateTaskRequest;
use App\Http\Controllers\Requests\v1\Tasks\IndexTasksRequest;
use App\Http\Controllers\Requests\v1\Tasks\UpdateTaskRequest;
use App\Models\Task;
use App\Repositories\Interfaces\TasksRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class TasksRepository implements TasksRepositoryInterface
{
    public function getUserTasks(IndexTasksRequest $request): LengthAwarePaginator
    {
        if ($searchTerm = $request->validated('filters.search')) {
            $q = Task::search($searchTerm)->where('user_id', auth()->user()->id);
        } else {
            $q = Task::where('user_id', '=', auth()->user()->id);
        }

        // Uncomment this conditions & result will be nested, like tree.
        //$q->whereNull('parent_id');
        //$q->with('children');

        if ($status = $request->validated('filters.status')) {
            $q->where('status', '=', $status);
        }
        if ($priority = $request->validated('filters.priority')) {
            $q->where('priority', '=', $priority);
        }

        if ($request->has('sort')) {
            foreach ($request->validated('sort') ?? [] as $field => $direction) {
                $q->orderBy($field, $direction);
            }
        }

        return $q->paginate(self::ITEMS_PER_PAGE);
    }

    public function createTask(CreateTaskRequest $request): Task
    {
        $task = new Task($request->validated());
        $task->user_id = auth()->user()->id;

        if ($task->status == Status::DONE->value) {
            $task->completed_at = Carbon::now();
        }
        if ($parentId = $request->validated('parent_id')) {
            $task->parent_id = $parentId;
        }

        if (!$task->save()) {
            throw new DatabaseOperationException('Unable to create the task. Try again later.');
        }

        return $task->refresh();
    }

    public function updateTask(Task $task, UpdateTaskRequest $request): Task
    {
        if ($title = $request->validated('title')) {
            $task->title = $title;
        }
        if ($priority = $request->validated('priority')) {
            $task->priority = $priority;
        }
        if ($description = $request->validated('description')) {
            $task->description = $description;
        }
        if ($parentId = $request->validated('parent_id')) {
            $task->parent_id = $parentId;
        }

        if (!$task->update()) {
            throw new DatabaseOperationException('Unable to update the task. Try again later.');
        }

        return $task;
    }

    public function completeUserTask(Task $task): Task
    {
        if (!$this->areAllChildrenDone($task)) {
            throw new TaskHasUndoneChildrenException(
                'Cannot mark task as done while dependent tasks are in "todo" status.'
            );
        }

        $task->status = Status::DONE->value;
        $task->completed_at = Carbon::now();

        if (!$task->update()) {
            throw new DatabaseOperationException(
                'Unable to complete the task. Try again later.'
            );
        }
        return $task;
    }

    public function markChildrenDone(Task $task): void
    {
        foreach ($task->children as $child) {
            $child->status = Status::DONE->value;
            $child->save();
            $this->markChildrenDone($child);
        }
    }

    protected function areAllChildrenDone(Task $task): bool
    {
        foreach ($task->children as $child) {
            if ($child->status !== Status::DONE->value) {
                return false;
            }

            if (!$this->areAllChildrenDone($child)) {
                return false;
            }
        }

        return true;
    }
}
