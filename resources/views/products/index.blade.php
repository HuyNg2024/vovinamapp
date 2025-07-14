<!DOCTYPE html>
<html>
<head>
    <title>Danh Sách Sản Phẩm</title>
</head>
<body>
    <h1>Danh Sách Sản Phẩm</h1>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Sản Phẩm</th>
                <th>Nhà Cung Cấp</th>
                <th>Giá</th>
                <th>Số Lượng Trong Kho</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product->ProductID }}</td>
                    <td>{{ $product->ProductName }}</td>
                    <td>{{ $product->SupplierID }}</td>
                    <td>{{ $product->UnitPrice }}</td>
                    <td>{{ $product->UnitsInStock }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
