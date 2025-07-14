<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\detail_order;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
   
    // Lấy danh sách sản phẩm
    public function index(Request $request)
{
    $lang = $request->input('lang', 'vi'); // Mặc định là tiếng Việt nếu không có tham số `lang`

    // Lấy tất cả sản phẩm
    $products = Product::all();

    // Duyệt qua từng sản phẩm và dịch dữ liệu theo ngôn ngữ
    $products->each(function($product) use ($lang) {
        if ($lang === 'en') {
            // Dịch dữ liệu sang tiếng Anh
            $product->ProductName = $product->tenenglish;
            $product->CategoryName = $product->CategoryNameEng;
            $product->SupplierName = $product->SupplierNameEng;
        }
        // Nếu `lang=vi`, giữ nguyên dữ liệu tiếng Việt, không cần thay đổi gì
    });

    return response()->json($products);
}
public function getallsp(Request $request)
{
    $lang = $request->input('lang', 'vi'); // Mặc định là tiếng Việt nếu không có tham số `lang`

    // Lấy tất cả sản phẩm và chỉ chọn các thuộc tính cần thiết
    $products = Product::select('ProductID', 'ProductName', 'SupplierID', 'UnitPrice', 'UnitsInStock', 'CategoryName', 'link_image', 'SupplierName', 'created_at', 'updated_at', 'sale', 'noibat')->get();

    // Duyệt qua từng sản phẩm và dịch dữ liệu theo ngôn ngữ
    $products->each(function($product) use ($lang) {
        if ($lang === 'en') {
            // Dịch dữ liệu sang tiếng Anh
            $product->ProductName = $product->tenenglish;
            $product->CategoryName = $product->CategoryNameEng;
            $product->SupplierName = $product->SupplierNameEng;
        }
        // Nếu `lang=vi`, giữ nguyên dữ liệu tiếng Việt, không cần thay đổi gì
    });

    return response()->json($products);
}


    // Lấy chi tiết sản phẩm
    public function show($id)
    {
        return Product::findOrFail($id);
    }

    // Tìm kiếm sản phẩm theo tên
    public function search($name)
    {
        return Product::where('ProductName', 'like', '%' . $name . '%')->get();
    }

    // Lọc sản phẩm theo nhà cung cấp, giá tiền
    // Lọc sản phẩm theo nhà cung cấp
    // Lọc sản phẩm theo nhà cung cấp
    public function filterBySupplier(Request $request)
    {
        $query = Product::query();

        if ($request->has('SupplierID') && $request->input('SupplierID') != '') {
            $query->where('SupplierID', $request->input('SupplierID'));
        }

        $products = $query->get();

        return response()->json($products);
    }

    // Lọc sản phẩm theo giá tiền (đổi sang POST)
    public function filterByPrice(Request $request)
    {
        $minPrice = floatval($request->input('min_price'));
        $maxPrice = floatval($request->input('max_price'));

        if ($minPrice < 0 || $maxPrice < 0 || $minPrice > $maxPrice) {
            return response()->json(['message' => 'Invalid input.'], 400);
        }

        $products = Product::whereBetween('UnitPrice', [$minPrice, $maxPrice])->get();

        return response()->json($products);
    }
    // Phương thức để lấy sản phẩm theo loại sản phẩm
    public function getByCategory($categoryname)
    {
        // Lấy sản phẩm theo CategoryName
        $products = Product::where('CategoryName', $categoryname)->get();

        // Trả về dữ liệu dưới dạng JSON
        return response()->json($products);
    }

    public function sortByFeatured(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'sort' => 'in:asc,desc', // Chỉ cho phép giá trị 'asc' hoặc 'desc'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid order parameter'], 400);
        }

        $query = Product::query();

        // Sắp xếp theo noibat theo thứ tự được chỉ định
        $sort = $request->input('sort', 'desc'); // Mặc định là 'desc' (giá trị noibat cao nhất trước)
        $query->orderBy('noibat', $sort);

        $products = $query->get();

        return response()->json($products);
    }

    public function sortByNew(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'sort' => 'in:asc,desc', // Chỉ cho phép giá trị 'asc' hoặc 'desc'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid order parameter'], 400);
        }

        $query = Product::query();

        // Sắp xếp theo created_at theo thứ tự được chỉ định
        $sort = $request->input('sort', 'desc'); // Mặc định là 'desc' (mới nhất)
        $query->orderBy('created_at', $sort);

        $products = $query->get();

        return response()->json($products);
    }

    public function sortBySale(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'sort' => 'in:asc,desc', // Chỉ cho phép giá trị 'asc' hoặc 'desc'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid sort parameter'], 400);
        }

        $query = Product::query();

        // Sắp xếp theo 'sale' theo thứ tự được chỉ định
        $sort = $request->input('sort', 'desc'); 
        $query->orderBy('sale', $sort); 

        $products = $query->get();

        return response()->json($products);
    }

    //Lấy các sp chạy nhất trong table_product
    public function getTopSellingProducts(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $topProducts = detail_order::select('id_product', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('id_product')
            ->orderBy('total_quantity', 'desc')
            //->limit(3)
            ->with('product') // Eager load product details
            ->get();

        $result = $topProducts->map(function ($item) use($lang) {
            return [
                'id_product' => $item->id_product,
                'total_quantity_sold' => $item->total_quantity,
                'ProductName' =>$lang === 'en'? $item->product->tenenglish : $item->product->ProductName,
                'product_price' => $item->product->UnitPrice,
                'link_image' => $item->product->link_image,
                'SupplierID' => $item->product->SupplierID,
                'SupplierName'=> $item->product->SupplierName,
                //'tenvi' =>$item->tenvi,
                //'tenenglish' =>$item->product->tenenglish,
            ];
        });

        return response()->json($result);
    }


    public function getSaleProductsSortedBySale2()
    {
        $products = Product::where('sale', '>', 0) 
            ->orderBy('sale', 'desc') 
            ->select('ProductID', 'ProductName', 'UnitPrice', 'CategoryName', 'SupplierName', 'UnitsInStock', 'sale', 'link_image','tenenglish') 
            ->get();

        return response()->json($products);
    }

    public function getSaleProductsSortedBySale(Request $request)
    {
        $lang = $request->query('lang', 'vi'); 

        $products = Product::where('sale', '>', 0) 
            ->orderBy('sale', 'desc') 
            ->select('ProductID', 'ProductName', 'UnitPrice', 'CategoryName', 'SupplierName', 'UnitsInStock', 'sale', 'link_image','tenenglish', 'CategoryNameEng', 'SupplierNameEng') 
            ->get();

        $products = $products->map(function ($product) use ($lang) {
            return [
                'ProductID' => $product->ProductID,
                'ProductName' => $lang === 'en' ? $product->tenenglish : $product->ProductName,
                'UnitPrice' => $product->UnitPrice,
                'CategoryName' => $lang === 'en' ? $product->CategoryNameEng : $product->CategoryName,
                'SupplierName' => $lang === 'en' ? $product->SupplierNameEng : $product->SupplierName,
                'UnitsInStock' => $product->UnitsInStock,
                'sale' => $product->sale,
                'link_image' => $product->link_image,
            ];
        });

        return response()->json($products);
    }

    
}

