<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="description" content="">
        <meta name="author" content="">
        <title>Thanh toán đơn hàng</title>
        <!-- Bootstrap core CSS -->
        <link href="{{asset('/vnpay/bootstrap.min.css')}}" rel="stylesheet"/>
        <!-- Custom styles for this template -->
        <link href="{{asset('/vnpay/jumbotron-narrow.css')}}" rel="stylesheet">  
        <script src="{{asset('/vnpay/jquery-1.11.3.min.js')}}"></script>
    </head>

    <body>
            
        <div class="container">
        <h3>Thanh toán học phí</h3>
            <div class="table-responsive">
                <form action="{{route('create_paymentCLB')}}" id="frmCreateOrder" method="post">
                    @csrf
                    <div class="form-group">
                    <label for="name_class">Tên lớp học</label>
                        <input class="form-control"
                                data-val="true"
                                data-val-required="The name field is required."
                                id="name_class"
                                name="name_class"
                                type="text"
                                value="{{$name_class}}"
                                readonly />
                                <br>

                                <label for="id">ID học viên</label>
                                <input class="form-control"
                                data-val="true"
                                data-val-number="The field Amount must be a number."
                                data-val-required="The Amount field is required."
                                id="amount"
                                max="100000000"
                                min="1"
                                name="member_id"
                                type="number"
                                value="{{$member_id}}"
                                readonly />
                                <br>

                                <label for="name_member">Tên học viên</label>
                                <input class="form-control"
                                    data-val="true"
                                    data-val-required="The name field is required."
                                    id="name_member"
                                    name="name_member"
                                    type="text"
                                    value="{{$name_member}}"
                                    readonly />
                                <br>


                                <label for="amount">Số tiền</label>      
                                <input class="form-control"
                                data-val="true"
                                data-val-number="The field Amount must be a number."
                                data-val-required="The Amount field is required."
                                id="amount"
                                max="100000000"
                                min="1"
                                name="amount"
                                type="number"
                                value="{{$giatien}}"
                                readonly />
                                <br>

                                <label for="class_id">ID lớp học</label>      
                                <input class="form-control"
                                data-val="true"
                                data-val-number="The field Amount must be a number."
                                data-val-required="The Amount field is required."
                                id="class_id"
                                max="100000000"
                                min="1"
                                name="class_id"
                                type="number"
                                value="{{$class_id}}"
                                readonly />
                                <br>
                                
                                <label for="id_classpayment">ID bảng trạng thái đăng ký lớp học</label>
                                <input class="form-control"
                                data-val="true"
                                data-val-number="The field Amount must be a number."
                                data-val-required="The Amount field is required."
                                id="id_classpayment"
                                name="id_classpayment"
                                type="number"
                                value="{{$id_classpayment}}"
                                readonly />

                                <br>
                    </div>
                     <h4>Chọn phương thức thanh toán</h4>
                    <div class="form-group">
                        <h5>Cách 1: Chuyển hướng sang Cổng VNPAY chọn phương thức thanh toán</h5>
                       <input type="radio" Checked="True" id="bankCode" name="bankCode" value="">
                       <label for="bankCode">Cổng thanh toán VNPAY</label><br>
                       
                    </div>
                    <div class="form-group">
                        <h5>Chọn ngôn ngữ giao diện thanh toán:</h5>
                         <input type="radio" id="language" Checked="True" name="language" value="vn">
                         <label for="language">Tiếng việt</label><br>
                         <input type="radio" id="language" name="language" value="en">
                         <label for="language">Tiếng anh</label><br>
                         
                    </div>
                    <button type="submit" class="btn btn-default" name="payment" value="2">Xác nhận thanh toán</button>
                </form>
                
            </div>
            <p>
                &nbsp;
            </p>
            <footer class="footer">
                <p>&copy; VNPAY 2020</p>
            </footer>
        </div>  
    </body>
</html>
