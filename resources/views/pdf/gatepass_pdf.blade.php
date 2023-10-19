<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Gatepass_{{ $sale['ship_ref'] }}</title>
    <link rel="stylesheet" href="{{ asset('/css/pass_style.css') }}" media="all" />
    {{-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script> --}}
    {{-- <script type="text/javascript" src="{{ asset('/js/qrcode.min.js') }}"></script> --}}
    <script type="text/javascript" src="{{ asset('/js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/qrcode.js') }}"></script>
    <script type="text/javascript">
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            width : 100,
            height : 100
        });

        function makeCode () {
            qrcode.makeCode({{ $sale['date'] }});
        }
        makeCode();
    </script>
</head>

<body>
    <header class="clearfix">
        {{-- <div id="logo">
            <img src="{{ asset('/images/' . $setting['logo']) }}">
        </div> --}}
        <div id="company">
            <div id="qrcode"></div>
        </div>
        <div id="Title-heading">
            Delivery Challan
        </div>
    </header>
    <header class="clearfix">
        <div id="Title-heading-company">
            <div class="company-name">{{ $setting['CompanyName'] }}</div>
            <div class="company-address-details">{{ $setting['CompanyAdress'] }}</div>
            {{-- <div class="company-details"><strong>S.T Registration No#: </strong> 12314{{ $setting['company_st'] }}</div>
            <div class="company-details"><strong>N.T.N: </strong> 12323{{ $setting['company_tax'] }}</div> --}}
        </div>
    </header>
    <main>
        <?php $qtySum = 0; ?>
        <div id="details" class="clearfix">
            <div id="client">
                <table class="table-sm">
                    {{-- <thead>
                        <tr>
                            <th class="desc">Customer Info</th>
                        </tr>
                    </thead> --}}
                    <tbody>
                        <tr>
                            <td>
                                <div><strong>M/S :</strong> {{ $sale['client_name'] }}</div>
                                {{-- <div><strong>Phone :</strong> {{ $sale['client_phone'] }}</div> --}}
                                <div><strong>Address :</strong> {{ $sale['client_adr'] }}</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="invoice">
                <table class="table-sm">
                    {{-- <thead>
                        <tr>
                            <th class="desc">Chalan Info</th>
                        </tr>
                    </thead> --}}
                    <tbody>
                        <tr>
                            <td>
                                <div><strong> Date: </strong>{{ $sale['date'] }}</div>
                                {{-- <div><strong> Sale Invoice: </strong> {{ $sale['Ref'] }} --}}
                                <div><strong> Gatepass No: </strong>{{ $sale['ship_ref'] }}</div>
                                <div><strong> Vehicle # : </strong> {{ $sale['vehicle_number'] }}</div>
                                <div><strong> Driver Name: </strong> {{ $sale['driver_name'] }}</div>
                                {{-- <div><strong> Payment Status : </strong> {{ $sale['payment_status'] }}</div> --}}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="details_inv">
            <table class="table-sm">
                <thead>
                    <tr style="text-align: center">
                        <th>Sr.</th>
                        <th>P.O. #</th>
                        <th>PRODUCT CODE</th>
                        <th>DESCRIPTION</th>
                        <th>QUANTITY</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($details as $detail)
                        <?php $i = 1; ?>
                        <tr>
                            <td style="text-align: center;">{{ $i++ }}</td>
                            <td style="text-align: center;">{{ $sale['po_number'] }}</td>
                            <td style="text-align: center;">{{ $detail['code'] }}</td>
                            <td style="text-align: center;">
                                <span>{{ $detail['name'] }} </span>
                                @if ($detail['is_imei'] && $detail['imei_number'] !== null)
                                    <p>IMEI/SN : {{ $detail['imei_number'] }}</p>
                                @endif
                            </td>
                            {{-- <td>{{ $detail['quantity'] }}/{{ $detail['unitSale'] }}</td> --}}
                            <td style="text-align: right;">{{ $detail['quantity'] }}</td>
                            <?php $qtySum += $detail['quantity']; ?>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div id="total">
            <table>
                {{-- <tr>
                    <td>Order Tax</td>
                    <td>{{ $sale['TaxNet'] }} </td>
                </tr>
                <tr>
                    <td>Discount</td>
                    <td>{{ $sale['discount'] }} </td>
                </tr>
                <tr>
                    <td>Shipping</td>
                    <td>{{ $sale['shipping'] }} </td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td>{{ $symbol }} {{ $sale['GrandTotal'] }} </td>
                </tr>

                <tr>
                    <td>Paid Amount</td>
                    <td>{{ $symbol }} {{ $sale['paid_amount'] }} </td>
                </tr>

                <tr>
                    <td>Due</td>
                    <td>{{ $symbol }} {{ $sale['due'] }} </td>
                </tr> --}}
                <tr>
                    <td><strong>Total Quantity:</strong></td>
                    <td>{{ $qtySum }} </td>
                </tr>
            </table>
        </div>
        <div id="signature">
            {{-- @if ($setting['is_invoice_footer'] && $setting['invoice_footer'] !== null)
                <p>{{ $setting['invoice_footer'] }}</p>
            @endif --}}
            <p>Out Time:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Received Signature</p>
        </div>
    </main>
</body>

</html>
