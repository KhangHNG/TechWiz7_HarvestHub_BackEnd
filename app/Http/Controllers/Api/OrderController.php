<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\FarmerNotAcceptingOrdersException;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderAuthorization;
use App\Services\OrderService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(
        OrderService $orderService,
        private OrderAuthorization $orderAuthorization,
    ) {
        $this->orderService = $orderService;
    }

    /**
     * GET /api/orders
     */
    public function index(Request $request): JsonResponse
    {
        $this->orderAuthorization->scopeIndex($this->actor($request), $request);

        $orders = $this->orderService->getOrders($request);

        if ($request->boolean('all')) {
            return response()->json([
                'success' => true,
                'message' => 'All orders retrieved successfully.',
                'data' => OrderResource::collection($orders),
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully.',
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ], 200);
    }

    /**
     * POST /api/orders
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $order = $this->orderService->createOrder($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully.',
                'data' => new OrderResource($order),
            ], 201);
        } catch (FarmerNotAcceptingOrdersException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/orders/{id}
     */
    public function findById(Request $request, $id): JsonResponse
    {
        $order = Order::with('items')->findOrFail($id);
        $this->orderAuthorization->assertCanView($this->actor($request), $order);

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully.',
            'data' => new OrderResource($order),
        ], 200);
    }

    /**
     * PUT /api/orders/{id}
     */
    public function update(UpdateOrderRequest $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validatedData = $request->validated();
        $this->orderAuthorization->assertCanUpdate($this->actor($request), $order, $validatedData);

        try {
            $order = $this->orderService->updateOrder($order, $validatedData);
            $orders = collect($this->orderService->placedOrders())
                ->each(fn (Order $placed) => $placed->loadMissing('items'));

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully.',
                'data' => new OrderResource($order->loadMissing('items')),
                'orders' => OrderResource::collection($orders),
            ], 200);
        } catch (InsufficientStockException|FarmerNotAcceptingOrdersException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/orders/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $this->orderAuthorization->assertCanDelete($this->actor($request), $order);

        try {
            $this->orderService->deleteOrder($order);

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order: '.$e->getMessage(),
            ], 400);
        }
    }

    private function actor(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'User information not found.',
            ], 404));
        }

        return $user;
    }
}
