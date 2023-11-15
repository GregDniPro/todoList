<?php

declare(strict_types=1);

namespace Api;

use App\Enums\Status;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Interfaces\TasksRepositoryInterface;
use App\Repositories\TasksRepository;
use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Codeception\Util\HttpCode;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\Support\ApiTester;

use function PHPUnit\Framework\assertFalse;

class TaskActionsCest
{
    private User $user;

    private Task $task;

    public function _before(ApiTester $I): void
    {
        $I->disableMiddleware(ThrottleRequests::class);
        $this->user = User::factory()->create();
        $this->task = Task::factory()->create([
            'user_id' => $this->user->id,
            'priority' => 1,
            'status' => Status::TODO
        ]);

        $this->seedTestData();

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
    }

    public function testIndex(ApiTester $I): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendGET('/v1/tasks');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'total' => 61,
            'per_page' => TasksRepositoryInterface::ITEMS_PER_PAGE,
        ]);
        assert(!empty($I->grabDataFromResponseByJsonPath('$.data')));
        assert(!empty($I->grabDataFromResponseByJsonPath('$.next_page_url')));
    }

    #[DataProvider('validIndexPayloadProvider')]
    public function testIndexValidPayload(ApiTester $I, Example $example): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendGET('/v1/tasks', $example['payload']);

        $I->seeResponseCodeIs(HttpCode::OK);
        assert(!empty($I->grabDataFromResponseByJsonPath('$.data')));
        assert(!empty($I->grabDataFromResponseByJsonPath('$.total')));
    }

    protected function validIndexPayloadProvider(): array
    {
        //TODO need to have separate elastic in the test env for filters.search testing
        //'filters.search' => 'test',
        return [
            [
                'payload' => [
                    'filters' => [
                        'priority' => 1,
                        'status' => Status::TODO->value,
                    ],
                ],
            ],
            [
                'payload' => [
                    'filters' => [
                        'priority' => 1,
                    ],
                ],
            ],
            [
                'payload' => [
                    'filters' => [
                        'status' => Status::TODO->value,
                    ],
                ],
            ],
            [
                'payload' => [
                    'sort' => [
                        'created_at' => 'desc',
                        'completed_at' => 'desc',
                        'priority' => 'desc',
                    ],
                ],
            ],
            [
                'payload' => [
                    'sort' => [
                        'created_at' => 'asc',
                        'completed_at' => 'asc',
                        'priority' => 'asc',
                    ],
                ],
            ],
            [
                'payload' => [
                    'sort' => [
                        'created_at' => 'desc',
                    ],
                ],
            ],
            [
                'payload' => [
                    'sort' => [
                        'completed_at' => 'desc',
                    ],
                ],
            ],
            [
                'payload' => [
                    'sort' => [
                        'priority' => 'desc',
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('invalidIndexPayloadProvider')]
    public function testIndexInvalidPayload(ApiTester $I, Example $example): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendGET('/v1/tasks', $example['payload']);

        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
    }

    protected function invalidIndexPayloadProvider(): array
    {
        return [
            [
                'payload' => [
                    'filters' => [
                        'priority' => 123,
                    ]
                ],
            ],
            [
                'payload' => [
                    'filters' => [
                        'status' => 'invalidStatus',
                    ],
                ]
            ],
            [
                'payload' => [
                    'sort' => [
                        'created_at' => 'some',
                        'completed_at' => 'invalid',
                        'priority' => 'order',
                    ],
                ],
            ],
            [
                'payload' => [
                    'sort' => [
                        'created_at' => 'some',
                    ],
                ],
            ],
            [
                'payload' => [
                    'sort' => [
                        'completed_at' => 'invalid',
                    ],
                ],
            ],
            [
                'payload' => [
                    'sort' => [
                        'priority' => 'order',
                    ],
                ],
            ],
        ];
    }

    public function testIndexNotAuthenticated(ApiTester $I): void
    {
        $I->sendGET('/v1/tasks');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function testShow(ApiTester $I): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendGet(sprintf('/v1/tasks/%s', $this->task->id));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(
            (new TaskResource($this->task))->response()->getData(true)
        );
    }

    public function testShowNotAuthenticated(ApiTester $I): void
    {
        $I->sendGet(sprintf('/v1/tasks/%s', $this->task->id));
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function testShowNotFound(ApiTester $I): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendGet('/v1/tasks/999999999');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function testStore(ApiTester $I): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendPOST('/v1/tasks', [
            'title' => 'Store new task',
            'priority' => 5,
            'description' => 'Store new task some text bla bla',
            'status' => Status::TODO->value,
        ]);
        $I->seeResponseCodeIs(HttpCode::CREATED);

        $storedTaskId = Task::find($I->grabDataFromResponseByJsonPath('$.data.id')[0]);
        $storedTask = (new TaskResource($storedTaskId))->response()->getData(true);
        $I->seeResponseContainsJson([
            'data' => array_filter($storedTask['data'], static function ($var) {
                return $var !== null;
            })
        ]);
    }

    #[DataProvider('validStorePayloadProvider')]
    public function testStoreValidPayload(ApiTester $I, Example $example): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendPOST('/v1/tasks', $example['payload']);
        $I->seeResponseCodeIs(HttpCode::CREATED);

        $storedTaskId = Task::find($I->grabDataFromResponseByJsonPath('$.data.id')[0]);
        $storedTask = (new TaskResource($storedTaskId))->response()->getData(true);
        $I->seeResponseContainsJson([
            'data' => array_filter($storedTask['data'], static function ($var) {
                return $var !== null;
            })
        ]);
    }

    protected function validStorePayloadProvider(): array
    {
        return [
            ['payload' => ['title' => 'Store another new task', 'priority' => 1]],
            [
                'payload' => [
                    'title' => 'Store new task',
                    'priority' => 2,
                    'description' => 'Store another new task with some text',
                    'status' => Status::DONE->value,
                ]
            ],
        ];
    }

    #[DataProvider('invalidStorePayloadProvider')]
    public function testStoreInvalidPayload(ApiTester $I, Example $example): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendPOST('/v1/tasks', $example['payload']);
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseContains('message');
        assert(!empty($I->grabDataFromResponseByJsonPath('$.message')));
    }

    protected function invalidStorePayloadProvider(): array
    {
        return [
            [
                'payload' => [
                    'priority' => 2,
                    'description' => 'Store another new task with some text',
                    'status' => Status::DONE->value,
                ]
            ],
            [
                'payload' => [
                    'title' => 'Store new task',
                    'description' => 'Store another new task with some text',
                    'status' => Status::DONE->value,
                ]
            ],
            [
                'payload' => [
                    'description' => 'Store another new task with some text',
                    'status' => Status::DONE->value,
                ]
            ],
        ];
    }

    public function testStoreNotAuthenticated(ApiTester $I): void
    {
        $I->sendPost('/v1/tasks');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    #[DataProvider('validUpdatePayloadProvider')]
    public function testUpdatePATCH(ApiTester $I, Example $example): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendPATCH(sprintf('/v1/tasks/%s', $this->task->id), $example['payload']);

        $I->seeResponseCodeIs(HttpCode::OK);
        foreach ($example['payload'] as $attribute => $value) {
            assertFalse($this->task->{$attribute} == $I->grabDataFromResponseByJsonPath("$.data.$attribute")[0]);
        }
    }

    #[DataProvider('validUpdatePayloadProvider')]
    public function testUpdatePUT(ApiTester $I, Example $example): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendPUT(sprintf('/v1/tasks/%s', $this->task->id), $example['payload']);

        $I->seeResponseCodeIs(HttpCode::OK);
        foreach ($example['payload'] as $attribute => $value) {
            assertFalse($this->task->{$attribute} == $I->grabDataFromResponseByJsonPath("$.data.$attribute")[0]);
        }
    }

    protected function validUpdatePayloadProvider(): array
    {
        return [
            [
                'payload' => [
                    'title' => 'testUpdatePatch title updated',
                    'priority' => 5,
                    'description' => 'testUpdatePatch description updated',
                    //parent_id
                ]
            ],
            [
                'payload' => [
                    'title' => 'testUpdatePatch title updated',
                ]
            ],
            [
                'payload' => [
                    'priority' => 5,
                ]
            ],
            [
                'payload' => [
                    'description' => 'testUpdatePatch description updated',
                ]
            ],
        ];
    }

    public function testUpdatePATCHNotAuthenticated(ApiTester $I): void
    {
        $I->sendPATCH(sprintf('/v1/tasks/%s', $this->task->id), [
            'title' => 'some updated title',
        ]);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function testUpdatePUTNotAuthenticated(ApiTester $I): void
    {
        $I->sendPUT(sprintf('/v1/tasks/%s', $this->task->id), [
            'title' => 'some updated title',
        ]);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function testDoneWithUndoneChildren(ApiTester $I): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendPATCH(sprintf('/v1/tasks/mark-done/%s', $this->task->id));
        $I->seeResponseCodeIs(HttpCode::INTERNAL_SERVER_ERROR);
        $I->seeResponseContainsJson([
            'message' => 'Cannot mark task as done while dependent tasks are in "todo" status.'
        ]);
    }

    public function testDoneWithDoneChildren(ApiTester $I): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        app(TasksRepository::class)->markChildrenDone($this->task);

        $I->sendPATCH(sprintf('/v1/tasks/mark-done/%s', $this->task->id));
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function testDoneNotAuthenticated(ApiTester $I): void
    {
        $I->sendPATCH(sprintf('/v1/tasks/mark-done/%s', $this->task->id));
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function testDestroy(ApiTester $I): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendDELETE(sprintf('/v1/tasks/%s', $this->task->id));

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        assertFalse(Task::where(['id' => $this->task->id])->exists());
    }

    public function testDestroyNotAuthenticated(ApiTester $I): void
    {
        $I->sendDELETE(sprintf('/v1/tasks/%s', $this->task->id));
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    private function seedTestData(): void
    {
        // Seed main task children with undone status
        Task::factory(5)->create([
            'parent_id' => $this->task->id,
            'user_id' => $this->user->id,
            'status' => Status::TODO,
        ]);

        // Seed user parent tasks
        Task::factory(5)->create([
            'parent_id' => null,
            'user_id' => $this->user->id,
            'status' => Status::TODO,
            'priority' => 1,
        ]);

        //Seed user children tasks
        Task::factory(50)->create([
            'user_id' => $this->user->id,
        ]);
    }
}
