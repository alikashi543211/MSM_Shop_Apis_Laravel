<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Shopping Cart Pdf</title>

    {{-- Popins Css --}}
    <style>
        /* devanagari */
        @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 400;
        src: url(https://fonts.gstatic.com/s/poppins/v20/pxiEyp8kv8JHgFVrJJbecmNE.woff2) format('woff2');
        unicode-range: U+0900-097F, U+1CD0-1CF6, U+1CF8-1CF9, U+200C-200D, U+20A8, U+20B9, U+25CC, U+A830-A839, U+A8E0-A8FB;
        }
        /* latin-ext */
        @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 400;
        src: url(https://fonts.gstatic.com/s/poppins/v20/pxiEyp8kv8JHgFVrJJnecmNE.woff2) format('woff2');
        unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
        }
        /* latin */
        @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 400;
        src: url(https://fonts.gstatic.com/s/poppins/v20/pxiEyp8kv8JHgFVrJJfecg.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }
    </style>


    <style type="text/css">

        * {

            font-family: Poppins,sans-serif !important;
            margin:0;
            padding:0;
        }
        table{

            width: 100% !important;

            font-size: x-small;

            text-align: left !important;

        }

        tfoot tr td{

            font-weight: bold;

            font-size: x-small;

        }

        .gray {

            background-color: lightgray

        }
        .items_tr .items_td{
            /* padding:15px; */
            /* border-bottom: 2px solid lightgrey; */
            font-size: 16px;
        }
        .items_quantity_td{
            /* border-right: 2px solid lightgrey; */
        }
        .items_price_td{
            /* border-left: 2px solid lightgrey; */
            text-align: left !important;

        }
        .items_table{
            border-collapse: collapse;
        }
        .hr_after_items{
            margin-top: 10px;
            margin-bottom: 10px;
            color: #cc0000 !important;
        }
        .hr_after_items_bank{
            margin-top : 12px;
            color: #cc0000 !important;
        }
        .items_table .last_items_tr .items_td{
            border-bottom: 0px solid;
        }
        .items_total_td
        {
            margin-bottom: 0px;
            font-weight:bold;
            color:black;
            margin-top:0px;
            margin-right:77px;
            text-align: right;
            font-size: 14px;
        }
        .header_table{
            background:#cc0000;
            padding-top: 30px;
            padding-bottom: 20px;
        }
        .shop_icon_div{
            height: 50px;
            width: 50px;
            background-color: #ffff;
            border-radius: 50%;
            display: inline-block;
            text-align: center;
        }
        .shop_icon_div img{
            margin-top: 12px;
        }
        .shop_title_col{
            background: white;
        }
        .shop_title_box{
            color:white;
            font-weight: bold;
            text-decoration: underline;
            font-size: 35px;
        }
        .small_text_box{
            color:#ffff;
            margin-top:12px;
        }
        .header_div{
            /* margin:0;
            padding:0; */
        }
        .ordered_text{
            text-align: center;
        }
        .barcode_col{
            text-align: left;
        }
        .barcode_col img{
            width: 150px;
            height: 30px;
        }
        .ordered_text_col{
            text-align: center;
            font-weight: bold;
            font-size:15px;
        }
        .address_box{
            border: 1px solid #cc0000;
            padding: 25px;
            font-size: 16px;
            min-height: 100px;
        }
        .delivery_inst_box{
            border-top: 1px solid #cc0000;
            border-right: 1px solid #cc0000;
            border-bottom: 1px solid #cc0000;
            padding: 25px;
            min-height: 100px;

        }
        .address_title{
            font-weight: bold;
            font-size: 18px;
            display: inline-block;
            margin-bottom: 4px;
        }
        .cart_items_box{
            border: 1px solid #cc0000;
            padding: 25px;
            font-size: 16px;
            min-height: 100px;
        }
        .our_new_margin{
            margin-top:25px;
            margin-bottom:25px;
        }
        .bank_card_box{
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            padding:25px;
        }
        .thank_you_text{
            color:#cc0000;
            font-size: 25px;
            font-weight: bold;
            text-align: center;
        }
        .bank_charge_table
        {
            margin-top: -35px;
        }
        /* Bottom Logo */
        .main_tag {
            position: relative;
        }
        .sub_div {
            position: absolute;
            bottom: 0px;
            margin-left:340px;
            margin-bottom:25px;
        }
        .collect_from_mailbox_button{
            top: 50%;
            left: 50%;
            /* transform: translate(-50%) */
        }
        .delivery_inst_title{
            display: inline-block;
            margin-top:22px;
        }

    </style>

</head>

<body>


    <main class="main_tag">
        <div class="header_div">
            <table width="100%" class="header_table">
                <tbody>
                    <tr>
                        <td width="5%">

                        </td>
                        <td width="7%">
                            <div class="shop_icon_div">
                                <img src="{{ public_path('images/shop_icon.png') }}" width="30px" class="" style="" />
                            </div>
                        </td>
                        <td width="13%">
                            <div class="shop_title_box">Shop</div>

                        </td>
                        <td width="50%">
                            <div class="small_text_box">
                                The most popular products at<br>
                                awesome prices
                            </div>
                        </td>
                        <td width="20%">
                            <img src="{{ public_path('images/default.png') }}" width="140px" class="" style="" />
                        </td>
                        <td width="5%">

                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <table width="100%" class="items_table our_new_margin">
            <tbody>
                <tr>
                    <td width="25%">

                    </td>
                    <td width="55%" class="ordered_text_col">
                        Ordered {{ date('F d, Y - h:i A', strtotime($inputs['created_at'])) }}
                    </td>
                    <td width="20%" class="barcode_col" style="text-align: center;margin-top:15px;">
                        @if(isset($barcode))
                            <img src="{{ $barcode }}" class="" style="" /><br>
                            <span style="color:grey;">{{ $barcode_text }}</span>
                        @endif
                    </td>
                    <td width="5%">

                    </td>
                </tr>
            </tbody>
        </table>
        <table width="100%" class="items_table our_new_margin">
            <tbody>
                <tr>
                    <td width="5%">

                    </td>
                    <td width="45%">
                        <span class="address_title">Address</span>
                        <div class="address_box">
                            {{ $inputs['name'] ?? '' }} - {{ $inputs['us_express_number'] ?? '' }}<br>
                            {{ $inputs['house_name'] ?? '' }} @if(isset($inputs['house_name'])) , @endif<br>
                            {{ $inputs['house_number'] ?? '' }} {{ $inputs['street'] ?? '' }} @if(isset($inputs['house_number']) || isset($inputs['street'])) , @endif<br>
                            {{ $inputs['parish'] ?? '' }} {{ $inputs['postal_code'] ?? '' }}
                        </div>
                    </td>
                    <td width="45%">
                        @if($inputs['delivery'])
                            <span class="address_title">
                                Delivery Instructions
                            </span>
                            <div class="delivery_inst_box">
                                {{ $inputs['instructions'] ?? '' }}
                            </div>
                        @else
                            <span class="address_title delivery_inst_title">
                            </span>
                            <div class="delivery_inst_box"  style="text-align:center;position:relative;">
                                <span style="position: absolute;left:25%;top:6%;font-size:18px;font-weight:bold;">Collect at Mailboxes</span>
                            </div>
                        @endif
                    </td>
                    <td width="5%">

                    </td>
                </tr>
            </tbody>
        </table>

        <table width="100%" class="items_table our_new_margin">
            <tbody>
                <tr>
                    <td width="5%">

                    </td>
                    <td width="90%">
                        <span class="address_title">Items Ordered</span>
                        <div class="cart_items_box">
                            <table width="100%">
                                <tbody>
                                    @foreach($inputs['items'] as $item)
                                        <tr class="items_tr @if($loop->iteration == count($inputs['items'])) last_items_tr @endif" width="100%">
                                            <td class="items_td" width="90%">
                                                {{ $item['quantity'] }} of {{ $item['description'] }} @if(isset($item['sku'])) - {{ $item['sku'] }} @endif
                                            </td>
                                            <td class="items_price_td items_td" width="10%">
                                                $ {{ twoDecimal($item['item_price_backend']) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                        {{-- Subtotal, Discount and Delivery Section --}}
                                        @if($landed_cost_overall > 0)
                                            <tr class="items_tr">
                                                <td colspan="2">
                                                    <hr class="hr_after_items">
                                                </td>
                                            </tr>
                                            <tr class="items_tr" width="100%">
                                                <td class="items_td" width="90%">
                                                    Subtotal
                                                </td>
                                                <td class="items_price_td items_td" width="10%">
                                                    $ {{ $subTotalBackend }}
                                                </td>
                                            </tr>
                                            <tr class="items_tr" width="100%">
                                                <td class="items_td" width="90%">
                                                    Discount
                                                </td>
                                                <td class="items_price_td items_td" width="10%">
                                                    $ -{{ twoDecimal($inputs['total_discount']) }}
                                                </td>
                                            </tr>
                                            @if(isset($inputs['delivery_fee']))
                                                <tr class="items_tr last_items_tr" width="100%">
                                                    <td class="items_td" width="90%">
                                                        Delivery
                                                    </td>
                                                    <td class="items_price_td items_td" width="10%">
                                                        $ {{ $inputs['delivery_fee'] }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                        {{-- Total Section --}}
                                        <tr class="items_tr">
                                            <td colspan="2">
                                                <hr class="hr_after_items">
                                            </td>
                                        </tr>
                                        <tr class="items_tr" width="100%">
                                            <td class="items_td" width="90%">
                                                Total
                                            </td>
                                            <td class="items_price_td items_td" width="10%">
                                                $ {{ twoDecimal($inputs['total_price']) }}
                                            </td>
                                        </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                    <td width="5%">

                    </td>
                </tr>
            </tbody>
        </table>

        @if(isset($inputs['card']))
            <table width="100%" class="items_table bank_charge_table">
                <tbody>
                    <tr>
                        <td width="5%">

                        </td>
                        <td width="90%" class="bank_card_box">
                            Charged to {{ $inputs['card']['card_type'] ?? '' }} xxxx {{ $inputs['card']['card_number'] ?? '' }}
                            <hr class="hr_after_items_bank">
                        </td>
                        <td width="5%">

                        </td>
                    </tr>
                </tbody>
            </table>
        @endif

        <p class="thank_you_text">
            Thanks for your order!
        </p>
        <br>
        <div class="sub_div">
            <p>
                <img src="{{ public_path('images/header-logo.png') }}" width="140px" class="moveimage" style="" />
            </p>
            <h1 style="font-size:15px;font-weight:bold;color:#cc0000;margin-top:8px;">
                You shop. We ship.
            </h1>
        </div>
    </main>

</body>

</html>
