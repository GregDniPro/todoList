<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\v1;

use App\Enums\Status;
use App\Http\Controllers\Requests\v1\Tasks\CreateTaskRequest;
use App\Http\Controllers\Requests\v1\Tasks\UpdateTaskRequest;
use App\Http\Controllers\v1\TaskController;
use App\Models\Task;
use App\Models\User;
use App\Repositories\TasksRepository;
use Codeception\Test\Unit;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Mockery;
use App\Http\Controllers\Requests\v1\Tasks\IndexTasksRequest;
use App\Http\Resources\TaskResource;

class TaskControllerTest extends Unit
{
    protected TaskController $taskController;
    protected TasksRepository $tasksRepository;

    protected function _before(): void
    {
        $this->tasksRepository = Mockery::mock(TasksRepository::class);
        $this->taskController = new TaskController($this->tasksRepository);
    }

    protected function _after(): void
    {
        Mockery::close();
    }

    public function testIndex(): void
    {
        // Create a mock request object & paginator.
        $request = Mockery::mock(IndexTasksRequest::class);
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        // Mock the tasks repository to return a paginated result.
        $this->tasksRepository
            ->shouldReceive('getUserTasks')
            ->with($request)
            ->andReturn($paginator); // Replace with a sample paginated data.

        $response = $this->taskController->index($request);
        $this->assertInstanceOf(TaskResource::class, $response);
    }

    public function testShow(): void
    {
        $task = Mockery::mock(Task::class);

        // Mock the 'load' method for loading related data.
        $task->shouldReceive('load')->with(['parent', 'children'])
            ->andReturn($task);

        // Mock the creation of a TaskResource.
        $taskResource = Mockery::mock(TaskResource::class)->shouldReceive('toArray')
            ->andReturn(['your', 'data', 'here']);

        // Mock the creation of a TaskResource instance.
        Mockery::mock(TaskResource::class)->shouldReceive('make')->with($task)
            ->andReturn($taskResource);

        // Mock the authorization check to allow access.
        Gate::shouldReceive('authorize')->with('view', $task)->andReturn(true);

        $response = $this->taskController->show($task);
        $this->assertInstanceOf(TaskResource::class, $response);
        $this->assertEquals(new TaskResource($task), $response);
    }

    public function testStore(): void
    {
        // Mock a sample create task request object & create task validated request data.
        $request = Mockery::mock(CreateTaskRequest::class);
        $requestData = [
            'title' => 'New Task',
            'description' => 'A description for the task',
            'status' => Status::DONE->value,
        ];
        $request->shouldReceive('validated')->andReturn($requestData);

        // Mock the authenticated user for the request.
        $authenticatedUser = Mockery::mock(User::class);
        Auth::shouldReceive('bla bla')->andReturn($authenticatedUser);
        $authenticatedUser->shouldReceive('id')->andReturn(1);

        // Mock the TasksRepository to create a task.
        $createdTask = Mockery::mock(Task::class);
        $this->tasksRepository->shouldReceive('createTask')->with($request)->andReturn($createdTask);

        // Mock the 'save' method on the created task.
        $createdTask->shouldReceive('save')->andReturn(true);

        $response = $this->taskController->store($request);
        $this->assertInstanceOf(TaskResource::class, $response);
    }

    public function testUpdate(): void
    {
        // Mock a sample update task request object & update task validated request data.
        $request = Mockery::mock(UpdateTaskRequest::class);
        $requestData = [
            'title' => 'Updated Task',
            'description' => 'An updated description for the task',
            'status' => Status::DONE->value,
            'priority' => 2,
        ];
        $request->shouldReceive('validated')->andReturn($requestData);

        // Create a mock task for testing.
        $task = Mockery::mock(Task::class);

        // Mock the authorization check to allow access.
        Gate::shouldReceive('authorize')->with('update', $task)->andReturn(true);

        // Mock the 'updateTask' method on the TasksRepository to update the task.
        $this->tasksRepository->shouldReceive('updateTask')->with($task, $request)->andReturn($task);

        $response = $this->taskController->update($task, $request);
        $this->assertInstanceOf(TaskResource::class, $response);
    }

    public function testDone(): void
    {
        $task = Mockery::mock(Task::class);

        // Mock the authorization check to allow access.
        Gate::shouldReceive('authorize')->with('markDone', $task)->andReturn(true);

        // Mock the 'completeUserTask' method on the TasksRepository to mark the task as done.
        $this->tasksRepository->shouldReceive('completeUserTask')->with($task)->andReturn($task);

        $response = $this->taskController->done($task);
        $this->assertInstanceOf(TaskResource::class, $response);
    }

    public function testDestroy(): void
    {
        $task = Mockery::mock(Task::class);

        // Mock the authorization check to allow access.
        Gate::shouldReceive('authorize')->with('delete', $task)->andReturn(true);

        // Mock the 'delete' method on the Task model.
        $task->shouldReceive('delete')->andReturn(true);

        $response = $this->taskController->destroy($task);

        // Assert that the response should be a JSON success message or an appropriate response for a successful deletion.
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(204, $response->getStatusCode());
    }
}
