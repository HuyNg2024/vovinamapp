<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>VNPAY RESPONSE</title>
    <link href="{{ asset('/vnpay/bootstrap.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('/vnpay/jumbotron-narrow.css') }}" rel="stylesheet">
    <script src="{{ asset('/vnpay/jquery-1.11.3.min.js') }}"></script>
</head>
<body>
    <div class="container">
        <div class="header clearfix">
            <h3 class="text-muted">VNPAY RESPONSE</h3>
        </div>
        <div class="table-responsive">
        <div class="form-group">
                <label>Mã người dùng:</label>
                <label>{{ $member_id }}</label>
            </div>

            <div class="form-group">
                <label>Tên người dùng:</label>
                <label>{{ $user_name }}</label>
            </div>
            <div class="form-group">
                <label>Mã đơn hàng:</label>
                <label>{{ $txn_ref }}</label>
            </div>
            
            <div class="form-group">
                <label>Số tiền:</label>
                <label>{{ $amount }}</label>
            </div>
            <div class="form-group">
                <label>Nội dung thanh toán:</label>
                <label>{{ $order_info }}</label>
            </div>
            <div class="form-group">
                <label>Mã phản hồi (vnp_ResponseCode):</label>
                <label>{{ $response_code }}</label>
            </div>
            <div class="form-group">
                <label>Mã GD Tại VNPAY:</label>
                <label>{{ $transaction_no }}</label>
            </div>
            <div class="form-group">
                <label>Mã Ngân hàng:</label>
                <label>{{ $bank_code }}</label>
            </div>
            <div class="form-group">
                <label>Thời gian thanh toán:</label>
                <label>{{ $pay_date }}</label>
            </div>
            <div class="form-group">
                <label>Kết quả:</label>
                <label>
                    @if ($status == 'thành công')
                        <span style="color:blue">GD Thành công</span>
                    @elseif ($status == 'thất bại')
                        <span style="color:red">GD Không thành công</span>
                    @else
                        <span style="color:red">Chữ ký không hợp lệ</span>
                    @endif
                </label>
            </div>
        </div>
        <p>&nbsp;</p>
        <footer class="footer">
            <p>&copy; VNPAY {{ date('Y') }}</p>
        </footer>
    </div>
</body>
</html>
