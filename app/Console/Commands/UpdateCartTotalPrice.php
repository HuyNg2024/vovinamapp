<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;
use App\Models\Product;

class UpdateCartTotalPrice extends Command
{
    protected $signature = 'cart:update-total-price';
    protected $description = 'Update total_price for existing cart items';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $cartItems = Cart::all();

        foreach ($cartItems as $cartItem) {
            $product = Product::find($cartItem->product_id);
            if ($product) {
                $cartItem->total_price = $product->UnitPrice * $cartItem->quantity;
                $cartItem->save();
            }
        }

        $this->info('Total price updated for all cart items.');
    }
}
