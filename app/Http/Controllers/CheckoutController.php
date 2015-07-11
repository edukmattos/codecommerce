<?php

namespace CodeCommerce\Http\Controllers;

use Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use CodeCommerce\Events\CheckoutEvent;

use CodeCommerce\Http\Requests;
use CodeCommerce\Http\Controllers\Controller;
use CodeCommerce\Order;
use CodeCommerce\OrderItem;
use CodeCommerce\Cart;
use CodeCommerce\Category;

class CheckoutController extends Controller
{
    public function __construct()
    {
    	$this->middleware('auth');
    } 

    public function place(Order $orderModel, OrderItem $ordemItem)
    {
    	if(!Session::has('cart'))
    	{
    		return "FALSE";
    	}

    	$cart = Session::get('cart');

        $categories = Category::all();

    	if ($cart->getTotal() > 0)
    	{
    	    $order = $orderModel->create(['user_id' => Auth::user()->id, 'total' => $cart->getTotal()]);

    		foreach ($cart->all() as $k=>$item) 
    		{
    			$order->items()->create(['product_id'=>$k, 'price'=>$item['price'], 'qtd'=>$item['qtd']]);
    		}
    		
    		$cart->clear();

    		event(new CheckoutEvent(Auth::user(), $order));

            return view('store.checkout', compact('cart', 'order', 'categories'));
        }
        else
        {
            return view('store.checkout', ['cart' => 'empty', 'categories' => $categories]);
        }
    }
}
