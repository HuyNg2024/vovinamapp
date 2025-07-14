<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Form</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        <h1>Submit Order</h1>
        <form action="{{ route('orders') }}" method="GET">
            
            <div class="form-group">
                <label for="id">Order ID:</label>
                <input type="text" id="orderId" name="id" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit Order</button>
        </form>
    </div>
</body>
</html>
