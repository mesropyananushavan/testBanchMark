<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTodoRequest;
use App\Http\Requests\Api\V1\UpdateTodoRequest;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Todos',
    description: 'CRUD operations for todo items'
)]
#[OA\Schema(
    schema: 'Todo',
    type: 'object',
    required: ['id', 'title', 'is_done', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Write API docs'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Prepare OpenAPI annotations'),
        new OA\Property(property: 'is_done', type: 'boolean', example: false),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-03-18T10:40:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-03-18T10:40:00Z'),
    ]
)]
#[OA\Schema(
    schema: 'TodoStoreRequest',
    type: 'object',
    required: ['title'],
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Write tests'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Add feature tests for todo CRUD'),
        new OA\Property(property: 'is_done', type: 'boolean', example: false),
    ]
)]
#[OA\Schema(
    schema: 'TodoUpdateRequest',
    type: 'object',
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Write integration tests'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Cover edge-cases'),
        new OA\Property(property: 'is_done', type: 'boolean', example: true),
    ]
)]
class TodoController extends Controller
{
    #[OA\Get(
        path: '/api/v1/todos',
        operationId: 'todosIndex',
        tags: ['Todos'],
        summary: 'List todos',
        description: 'Returns all todos. Optional status filter: done|pending.',
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter by completion status',
                schema: new OA\Schema(type: 'string', enum: ['done', 'pending'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Todos list',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Todo')
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Todo::query()->orderByDesc('id');

        $status = $request->query('status');
        if ($status === 'done') {
            $query->where('is_done', true);
        }

        if ($status === 'pending') {
            $query->where('is_done', false);
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/todos',
        operationId: 'todosStore',
        tags: ['Todos'],
        summary: 'Create todo',
        description: 'Creates a new todo item.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TodoStoreRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Todo created',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Todo'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreTodoRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['is_done'] = $validated['is_done'] ?? false;

        $todo = Todo::query()->create($validated);

        return response()->json([
            'data' => $todo,
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/todos/{todo}',
        operationId: 'todosShow',
        tags: ['Todos'],
        summary: 'Get single todo',
        parameters: [
            new OA\Parameter(
                name: 'todo',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Todo detail',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Todo'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Todo not found'),
        ]
    )]
    public function show(Todo $todo): JsonResponse
    {
        return response()->json([
            'data' => $todo,
        ]);
    }

    #[OA\Put(
        path: '/api/v1/todos/{todo}',
        operationId: 'todosUpdate',
        tags: ['Todos'],
        summary: 'Update todo',
        parameters: [
            new OA\Parameter(
                name: 'todo',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TodoUpdateRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Todo updated',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Todo'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Todo not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateTodoRequest $request, Todo $todo): JsonResponse
    {
        $todo->fill($request->validated());
        $todo->save();

        return response()->json([
            'data' => $todo,
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/todos/{todo}',
        operationId: 'todosDelete',
        tags: ['Todos'],
        summary: 'Delete todo',
        parameters: [
            new OA\Parameter(
                name: 'todo',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Todo deleted'),
            new OA\Response(response: 404, description: 'Todo not found'),
        ]
    )]
    public function destroy(Todo $todo): Response
    {
        $todo->delete();

        return response()->noContent();
    }
}
