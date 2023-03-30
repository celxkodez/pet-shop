<html>
<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        p {
            margin: 0pt;
        }

        table.items {
            border: 0.1mm solid #e7e7e7;
        }

        td {
            vertical-align: top;
        }

        .items td {
            border-left: 0.1mm solid #e7e7e7;
            border-right: 0.1mm solid #e7e7e7;
        }

        table thead td {
            text-align: center;
            border: 0.1mm solid #e7e7e7;
        }

        .items td.blanktotal {
            background-color: #EEEEEE;
            border: 0.1mm solid #e7e7e7;
            background-color: #FFFFFF;
            border: 0mm none #e7e7e7;
            border-top: 0.1mm solid #e7e7e7;
            border-right: 0.1mm solid #e7e7e7;
        }

        .items td.totals {
            text-align: right;
            border: 0.1mm solid #e7e7e7;
        }

        .items td.cost {
            text-align: "."center;
        }
    </style>
</head>

<body>
<table width="100%" style="font-family: sans-serif;" cellpadding="10">
    <tr>
        <td width="100%" style="text-align: center; font-size: 20px; font-weight: bold; padding: 0px;">
            INVOICE
        </td>
    </tr>
    <tr>
        <td height="10" style="font-size: 0px; line-height: 10px; height: 10px; padding: 0px;">&nbsp;</td>
    </tr>
</table>
<table width="100%" style="font-family: sans-serif;" cellpadding="10">
    <tr>
        <td style="border: 0.1mm solid #eee;">
            <h6 class="mb-3">CustomerDetails: </h6>
            <div>
                <strong>{{$order->user->first_name }} {{$order->user->last_name }}</strong>
            </div>
            <div>ID:{{$order->user->uuid }}</div>
            <div>Phone number:{{$order->user->phone_number }}</div>
            <div>Email:{{$order->user->email }}</div>
            <div>Address:{{$order->user->addres }}</div>

        </td>
        <td width="2%">&nbsp;</td>
        <td width="49%" style="border: 0.1mm solid #eee; text-align: right;">
            <h6 class="mb-3">Billing/Shipping Address:</h6>

            <div>Billing: {{json_decode($order->address, true)['billing'] }}</div>
            <div>Shipping: {{json_decode($order->address, true)['shipping'] }}</div>
        </td>
    </tr>
</table>
<br>
<table width="100%" style="font-family: sans-serif; font-size: 14px;" >
    <tr>
        <td>
            <table width="60%" align="left" style="font-family: sans-serif; font-size: 14px;" >
                <tr>
                    <td style="padding: 0px; line-height: 20px;">&nbsp;</td>
                </tr>
            </table>
            <table width="40%" align="right" style="font-family: sans-serif; font-size: 14px;" >
                <tr>
                    <td width="10%" style="border: 1px #eee solid; padding: 0px 8px; line-height: 20px;"><strong>Order ID</strong></td>
                    <td width="90%" style="border: 1px #eee solid; padding: 0px 8px; line-height: 20px;">{{$order->uuid}}</td>
                </tr>

                <tr>
                    <td width="10%" style="border: 1px #eee solid; padding: 0px 8px; line-height: 20px;"><strong>Status</strong></td>
                    <td width="90%" style="border: 1px #eee solid; padding: 0px 8px; line-height: 20px;">{{$order->orderStatus->title}}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class="items" width="100%" style="font-size: 14px; border-collapse: collapse;" cellpadding="8">
    <thead>
    <tr>
        <td width="1%" style="text-align: left;"><strong>#</strong></td>
        <td width="14%" style="text-align: left;"><strong>ID</strong></td>
        <td width="45%" style="text-align: left;"><strong>Item Name</strong></td>
        <td width="13.3%" style="text-align: left;"><strong>Unit Price</strong></td>
        <td width="13.3%" style="text-align: left;"><strong>Quantity</strong></td>
        <td width="13.3%" style="text-align: left;"><strong>Price</strong></td>
    </tr>
    </thead>
    <tbody>
    <!-- ITEMS HERE -->
        @php
            $products = (array)json_decode($order->products, true);

            $productCollectionFromArray = collect($products);

            $products = App\Models\Product::whereIn('uuid', $productCollectionFromArray
                ->pluck('uuid')
                ->toArray()
            )->get();

            $totalPrice = 0;
        @endphp

        @foreach($products as $product)
            @php
                $quantity = $productCollectionFromArray->where('uuid', $product->uuid)->first()['quantity'];
                $totalPrice += $quantity * $product->price;
            @endphp
            <tr>
                <td class="center">{{$loop->iteration}}</td>
                <td class="left strong">{{$product->uuid}}</td>
                <td class="left">{{$product->title}}</td>

                <td class="right">{{$product->price}}</td>
                <td class="center">{{$quantity}}</td>
                <td class="right">{{$quantity * $product->price}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<br>
<table width="100%" style="font-family: sans-serif; font-size: 14px;" >
    <tr>
        <td>
            <table width="60%" align="left" style="font-family: sans-serif; font-size: 14px;" >
                <tr>
                    <td style="padding: 0px; line-height: 20px;">&nbsp;</td>
                </tr>
            </table>
            <table width="40%" align="right" style="font-family: sans-serif; font-size: 14px;" >
                <tr>
                    <td style="border: 1px #eee solid; padding: 0px 8px; line-height: 20px;"><strong>Subtotal</strong></td>
                    <td style="border: 1px #eee solid; padding: 0px 8px; line-height: 20px;">{{$totalPrice}}</td>
                </tr>
                <tr>
                    <td style="border: 1px #eee solid; padding: 0px 8px; line-height: 20px;"><strong>Delivery Fee</strong></td>
                    <td style="border: 1px #eee solid; padding: 0px 8px; line-height: 20px;">{{$order->delivery_fee}}</td>
                </tr>
                <tr>
                    <td style="border: 1px #eee solid; padding: 0px 8px; line-height: 20px;"><strong>Total</strong></td>
                    <td style="border: 1px #eee solid; padding: 0px 8px; line-height: 20px;">{{$totalPrice + $order->delivery_fee}}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table width="100%" style="font-family: sans-serif; font-size: 14px;" >
    <br>
    <tr>
        <td>
            <table width="25%" align="left" style="font-family: sans-serif; font-size: 14px;" >
                <tr>
                    <td style="padding: 0px; line-height: 20px;">
                        &nbsp;
                    </td>
                </tr>
            </table>
            <table width="50%" align="left" style="font-family: sans-serif; font-size: 13px; text-align: center;" >
                <tr>
                    <td style="padding: 0px; line-height: 20px;">
                        <strong>Pet Shop</strong>
                    </td>
                </tr>
            </table>
            <table width="25%" align="right" style="font-family: sans-serif; font-size: 14px;" >
                <tr>
                    <td style="padding: 0px; line-height: 20px;">
                        &nbsp;
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <br>
</table>
</body>
</html>
