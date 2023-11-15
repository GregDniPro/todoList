<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Enums\Status;
use App\Http\Controllers\Requests\v1\Tasks\CreateTaskRequest;
use App\Http\Controllers\Requests\v1\Tasks\IndexTasksRequest;
use App\Http\Controllers\Requests\v1\Tasks\UpdateTaskRequest;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Interfaces\TasksRepositoryInterface;
use App\Repositories\TasksRepository;
use Carbon\Carbon;
use Codeception\Attribute\Skip;
use Codeception\Test\Unit;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class TasksRepositoryTest extends Unit
{
    use MockeryPHPUnitIntegration;

    protected TasksRepository $tasksRepository;

    protected function _before()
    {
        $this->tasksRepository = new TasksRepository();
    }

    #[Skip('need to be reworked, problems with DB isolation')]
    public function testGetUserTasks(): void
    {
        // Create a sample user & and simulate the authenticated.
        $user = User::factory()->make();
        Auth::shouldReceive('user')->andReturn($user);

        $request = Mockery::mock(IndexTasksRequest::class);
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        // Mock Eloquent model interactions.
        $task = Mockery::mock(Task::class);
        $task->shouldReceive('where')->with('user_id', $user->id)->andReturnSelf();
        $task->shouldReceive('paginate')->with(TasksRepositoryInterface::ITEMS_PER_PAGE)->andReturn($paginator);

        // Mock the validated method to return an empty array.
        $request->shouldReceive('validated')->with('filters.search')->andReturn('');
        $request->shouldReceive('validated')->with('filters.priority')->andReturn(1);
        $request->shouldReceive('validated')->with('filters.status')->andReturn(Status::DONE->value);
        $request->shouldReceive('validated')->with('sort')->andReturn(['created_at' => 'desc']);
        $request->shouldReceive('has')->andReturn(true);

        // Call the getUserTasks method and assert the result.
        $result = $this->tasksRepository->getUserTasks($request);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    #[Skip('need to be reworked, problems with DB isolation')]
    public function testCreateTask(): void
    {
        $this->tasksRepository = Mockery::mock(TasksRepository::class);

        // Mock the created task.
        $createdTask = Mockery::mock(Task::class);
        $createdTask->shouldReceive('save')->andReturn(true);
        $createdTask->shouldReceive('setAttribute');
        $createdTask->shouldReceive('getAttribute')->with('id')->andReturn(123); // Adjust this line based on your actual use case
        $createdTask->shouldReceive('getAttribute')->with('title')->andReturn('New Task');
        $createdTask->shouldReceive('getAttribute')->with('description')->andReturn('A description for the task');
        $createdTask->shouldReceive('getAttribute')->with('priority')->andReturn(1);
        $createdTask->shouldReceive('getAttribute')->with('status')->andReturn(Status::DONE->value);
        $createdTask->shouldReceive('getAttribute')->with('completed_at')->andReturn(Carbon::now());

        $this->tasksRepository->shouldReceive('createTask')->andReturn($createdTask);

        $res = $this->tasksRepository->createTask(Mockery::mock(CreateTaskRequest::class));
        $this->assertInstanceOf(Task::class, $res);
        $this->assertEquals('New Task', $res->title);
        $this->assertEquals('A description for the task', $res->description);
        $this->assertEquals(1, $res->priority);
        $this->assertEquals(Status::DONE->value, $res->status);
        $this->assertInstanceOf(Carbon::class, $res->completed_at);
    }

    public function testUpdateTask(): void
    {
        $request = Mockery::mock(UpdateTaskRequest::class);
        $requestTitle = 'Updated Task';
        $requestDescription = 'An updated description for the task';
        $requestPriority = 2;
        $requestStatus = Status::TODO->value;

        // Mock a sample request for updating.
        $request->shouldReceive('validated')->with('title')->andReturn($requestTitle);
        $request->shouldReceive('validated')->with('priority')->andReturn($requestPriority);
        $request->shouldReceive('validated')->with('description')->andReturn($requestDescription);
        $request->shouldReceive('validated')->with('parent_id')->andReturn(null);

        // Mock a sample task for updating.
        $task = Mockery::mock(Task::class);
        $task->shouldReceive('update')->andReturn(true);
        $task->shouldReceive('setAttribute')->andReturn(true);
        $task->shouldReceive('getAttribute')->with('title')->andReturn($requestTitle);
        $task->shouldReceive('getAttribute')->with('description')->andReturn($requestDescription);
        $task->shouldReceive('getAttribute')->with('status')->andReturn($requestStatus);
        $task->shouldReceive('getAttribute')->with('priority')->andReturn($requestPriority);
        $task->shouldReceive('getOriginal')->with('status')->andReturn(Status::DONE->value);

        $result = $this->tasksRepository->updateTask($task, $request);
        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals('Updated Task', $result->title);
        $this->assertEquals('An updated description for the task', $result->description);
        $this->assertEquals($requestPriority, $result->priority);
        $this->assertEquals(Status::TODO->value, $result->status);
    }

    public function testCompleteUserTask(): void
    {
        $task = Mockery::mock(Task::class);
        $task->shouldReceive('update')->andReturn(true);
        $task->shouldReceive('setAttribute')->andReturn(true);
        $task->shouldReceive('getAttribute')->with('status')->andReturn(Status::DONE->value);
        $task->shouldReceive('getAttribute')->with('completed_at')->andReturn(Carbon::now());

        // Create a partial mock for the TasksRepository class and allow mocking of protected methods.
        $partialMockRepository = Mockery::mock(TasksRepository::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $partialMockRepository
            ->shouldReceive('areAllChildrenDone')
            ->with($task)
            ->andReturn(true);

        $result = $partialMockRepository->completeUserTask($task);
        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals(Status::DONE->value, $result->status);
        $this->assertInstanceOf(Carbon::class, $result->completed_at);
        $this->assertInstanceOf(Carbon::class, $result->completed_at);
        $this->assertNotNull($result->completed_at);
    }

    public function testAreAllChildrenDoneReturnsTrueWhenAllChildrenAreDone(): void
    {
        $task = new Task();
        $child1 = new Task(['status' => Status::DONE->value]);
        $child2 = new Task(['status' => Status::DONE->value]);
        $task->children = [$child1, $child2];

        // Create a partial mock for the TasksRepository class.
        $partialMockRepository = Mockery::mock(TasksRepository::class)->makePartial();

        // Use ReflectionClass to access the protected method.
        $reflectionClass = new \ReflectionClass(TasksRepository::class);
        $method = $reflectionClass->getMethod('areAllChildrenDone');
        $method->setAccessible(true);

        // Call the protected method and assert the result.
        $result = $method->invoke($partialMockRepository, $task);

        // Assert that all children are done, so the result should be true.
        $this->assertTrue($result);
    }

    public function testAreAllChildrenDoneReturnsFalseWhenAtLeastOneChildIsNotDone(): void
    {
        $task = new Task();
        $child1 = new Task(['status' => Status::DONE->value]);
        $child2 = new Task(['status' => Status::TODO->value]);
        $task->children = [$child1, $child2];

        // Create a partial mock for the TasksRepository class.
        $partialMockRepository = Mockery::mock(TasksRepository::class)->makePartial();

        // Use ReflectionClass to access the protected method.
        $reflectionClass = new \ReflectionClass(TasksRepository::class);
        $method = $reflectionClass->getMethod('areAllChildrenDone');
        $method->setAccessible(true);

        // Call the protected method and assert the result.
        $result = $method->invoke($partialMockRepository, $task);

        // Assert that not all children are done, so the result should be false.
        $this->assertFalse($result);
    }
}
