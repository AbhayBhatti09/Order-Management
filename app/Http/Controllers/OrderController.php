<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Mail\OrderShipped;
use Illuminate\Support\Facades\Mail;
use App\Services\PayPalService;



class OrderController extends Controller
{
    //

    public function store(Request $request,PayPalService $paypalService){
        if (Auth::user()?->is_admin == 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }
        $user = auth()->user();
        $totalPrice = 0;
        $orderItems = [];
        
        foreach($request->items as $item){
            $product=Product::findOrFail($item['product_id']);
            
            // return response()->json([
            //     'data'=>$product
            // ]);
            if($product->stock < $item['quantity']){
                return response()->json([
                    'error' =>"Not enough stock for {$product->name}, available stocks only {$product->stock}"
                ],400);
            }

            $orderItems[]=[
                'product_id'=>$product->id,
                'quantity'=>$item['quantity'],
                'price'=>$product->price,

            ];
            $totalPrice += $product->price * $item['quantity'];
            //$product->decrement('stock', $item['quantity']);

        }
        $paymentResponse = $paypalService->createPayment($totalPrice);
        // return response()->json([
        //     'data'=>$paymentResponse
        // ]);

        if (!isset($paymentResponse['id'])) {
            return response()->json(['error' => 'Payment failed'], 400);
        }

        $order=Order::create([
            'user_id' => $user->id,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        foreach ($orderItems as &$item) {
            $product->decrement('stock', $item['quantity']);

            $item['order_id'] = $order->id;
        }
    
        OrderItem::insert($orderItems);
        return response()->json([
        'message' => 'Order placed successfully',
         'order' => $order,
         'payment_id' => $paymentResponse['id'],
         'approval_url' => $paymentResponse['links'][1]['href'] // Redirect user for payment approval
 
        ], 201);

      
    }

    public function index()
    {
        $orders = auth()->user()->orders()->with('orderItems.product')->get();
        return response()->json($orders);
    }

    public function show($id){
        
        $order1=Order::find($id);
        if($order1){
            $order=Order::with('orderItems.product')->where('user_id',auth()->id())->findOrFail($id);
            return response()->json([
                'Order'=>$order
            ],200);
        }else{
            return response()->json([
                'error'=>'Order Not Found!!!'
            ],400);
            
        }
      
    }


    public function updateStatus(Request $request,$id){
    
        if (auth()->user()->is_admin != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [

            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'

        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }
        $order = Order::findOrFail($id);

        $order->update(['status' => $request->status]);

        if ($request->status === 'shipped') {
            Mail::to($order->user->email)->send(new OrderShipped($order));
            return response()->json(['message' => 'Order status updated successfully']);
        }
        if($request->status==='cancelled'){
            $order = Order::with('orderItems.product')->findOrFail($id);
            foreach ($order->orderItems as $item) {
                $item->product->increment('stock', $item->quantity);
            }
    
            return response()->json([
                'message' => 'Order cancelled and stock restored successfully',
                'order_id' => $order->id
            ]);
        }




    }


    public function capturePayment(Request $request, PayPalService $paypalService)
{
    $orderId = $request->input('order_id');
    $paymentId = $request->input('payment_id');

    $paymentResponse = $paypalService->capturePayment($paymentId);

    if (!isset($paymentResponse['status']) || $paymentResponse['status'] !== 'COMPLETED') {
        return response()->json(['error' => 'Payment capture failed'], 400);
    }

    // Update order status to "processing"
    $order = Order::findOrFail($orderId);
    $order->update(['status' => 'processing']);

    return response()->json([
        'message' => 'Payment successful, order is now processing',
        'order_id' => $order->id
    ]);
}

}
