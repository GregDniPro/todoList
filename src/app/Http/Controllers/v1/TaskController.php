<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1;

use App\Exceptions\DatabaseOperationException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Interfaces\v1\TaskControllerInterface;
use App\Http\Controllers\Requests\v1\Tasks\CreateTaskRequest;
use App\Http\Controllers\Requests\v1\Tasks\IndexTasksRequest;
use App\Http\Controllers\Requests\v1\Tasks\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\OpenApi\Parameters\CreateTaskParameters;
use App\OpenApi\Parameters\ListTasksParameters;
use App\OpenApi\Parameters\UpdateTaskParameters;
use App\OpenApi\Responses\DeleteTaskResponse;
use App\OpenApi\Responses\ListTasksResponse;
use App\OpenApi\Responses\ShowTaskResponse;
use App\OpenApi\Responses\ShowTaskWithRelationsResponse;
use App\OpenApi\SecuritySchemes\BearerTokenSecurityScheme;
use App\Repositories\Interfaces\TasksRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Vyuldashev\LaravelOpenApi\Attributes\Operation;
use Vyuldashev\LaravelOpenApi\Attributes\Parameters;
use Vyuldashev\LaravelOpenApi\Attributes\PathItem;
use Vyuldashev\LaravelOpenApi\Attributes\Response;

#[PathItem]
class TaskController extends Controller implements TaskControllerInterface
{
    public function __construct(
        protected TasksRepositoryInterface $tasksRepository
    ) {
    }

    /**
     * Get list of users tasks.
     *
     * Gets list of users tasks by filters.
     */
    #[Operation(security: BearerTokenSecurityScheme::class)]
    #[Parameters(factory: ListTasksParameters::class)]
    #[Response(factory: ListTasksResponse::class)]
    public function index(IndexTasksRequest $request): TaskResource
    {
        $paginated = $this->tasksRepository->getUserTasks(
            $request
        );

        return new TaskResource($paginated);
    }

    /**
     * Show user task.
     *
     * Shows users task with relations.
     */
    #[Operation(security: BearerTokenSecurityScheme::class)]
    #[Response(factory: ShowTaskWithRelationsResponse::class)]
    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        $task->load(['parent', 'children']);

        return new TaskResource($task);
    }

    /**
     * Create new task.
     *
     * Stores users new task.
     */
    #[Operation(security: BearerTokenSecurityScheme::class)]
    #[Parameters(factory: CreateTaskParameters::class)]
    #[Response(factory: ShowTaskResponse::class)]
    public function store(CreateTaskRequest $request): TaskResource
    {
        $task = $this->tasksRepository->createTask(
            $request
        );

        return new TaskResource($task);
    }

    /**
     * Update task.
     *
     * Updates users task.
     */
    #[Operation(security: BearerTokenSecurityScheme::class)]
    #[Parameters(factory: UpdateTaskParameters::class)]
    #[Response(factory: ShowTaskResponse::class)]
    public function update(Task $task, UpdateTaskRequest $request): TaskResource
    {
        $this->authorize('update', $task);

        $task = $this->tasksRepository->updateTask(
            $task,
            $request
        );

        return new TaskResource($task);
    }

    /**
     * Mark task as done.
     *
     * Marks users task as done.
     */
    #[Operation(security: BearerTokenSecurityScheme::class)]
    #[Response(factory: ShowTaskResponse::class)]
    public function done(Task $task): TaskResource
    {
        $this->authorize('markDone', $task);

        $task = $this->tasksRepository->completeUserTask(
            $task
        );

        return new TaskResource($task);
    }

    /**
     * Delete task.
     *
     * Deletes users task.
     */
    #[Operation(security: BearerTokenSecurityScheme::class)]
    #[Response(factory: DeleteTaskResponse::class)]
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        if (!$task->delete()) {
            throw new DatabaseOperationException(
                'Unable to delete the task. Try again later.'
            );
        }
        return response()->json([], 204);
    }
}
