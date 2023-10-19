<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Sales-Invoice-{{ $sale['Ref'] }}</title>
    <link rel="stylesheet" href="{{ asset('/css/pdf_style.css') }}" media="all" />
</head>

<body>
    <header class="clearfix">
        {{-- <div id="logo">
            <img src="{{ asset('/images/' . $setting['logo']) }}">
        </div> --}}
        <div id="company">
            <div><strong> Status : </strong> {{ $sale['statut'] }}</div>
            <div><strong> Payment Status : </strong> {{ $sale['payment_status'] }}</div>
        </div>
        <div id="Title-heading">
            BILL
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
        <div id="details" class="clearfix">
            <div id="invoice">
                <table class="table-sm">
                    {{-- <thead>
                        <tr>
                            <th class="desc"><strong>Invoice No :</strong> {{ $sale['Ref'] }}</th>
                        </tr>
                    </thead> --}}
                    <tbody>
                        <tr>
                            <td>
                                <div><strong>Bill No :</strong> {{ $sale['Ref'] }}</div>
                                <div> {{ " " }}</div>
                                <div><strong>Bill Date : </strong>{{ $sale['date'] }}</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="client">
                <table class="table-sm">
                    {{-- <thead>
                        <tr>
                            <th class="desc">Customer Information</th>
                        </tr>
                    </thead> --}}
                    <tbody>
                        <tr>
                            <td>
                                <div><strong>Buyer's Name :</strong> {{ $sale['client_name'] }}</div>
                                {{-- <div>
                                    @if ($sale['sales_tax_number'])
                                        <span><strong>ST Registration No. :</strong> {{ $sale['sales_tax_number'] }}</span>
                                    @endif
                                    @if ($sale['client_tax'])
                                        <span style="padding-left: 20px;"><strong>N.T.N :</strong> {{ $sale['client_tax'] }}</span>
                                    @endif
                                </div> --}}
                                {{-- <div><strong>Phone :</strong> {{ $sale['client_phone'] }}</div>
                                                    <div><strong>Email :</strong> {{ $sale['client_email'] }}</div> --}}
                                <div><strong>Address :</strong> {{ $sale['client_adr'] }}</div>
                                <div>{{ " " }}</div>
                                <div><strong> PO Number : </strong> {{ $sale['po_number'] }}</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="details_inv">
            <table class="table-sm">
                <thead>
                    <tr>
                        <th>Sr.</th>
                        <th>Description of Goods</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Exclusive S.T Value</th>
                        <th>Sales Tax Rate (%)</th>
                        <th>Sales Tax Value</th>
                        <th>Inclusive S.T Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $qtyTotal = 0;
                    $exSTTotal = 0;
                    $stTotal = 0;
                    $totalT = 0;
                    ?>
                    @foreach ($details as $detail)
                        <tr>
                            <td style="text-align: center;">{{ $i++ }}</td>
                            <td>
                                <span>{{ $detail['code'] }} ({{ $detail['name'] }})</span>
                                @if ($detail['is_imei'] && $detail['imei_number'] !== null)
                                    <p>IMEI/SN : {{ $detail['imei_number'] }}</p>
                                @endif
                            </td>
                            <td style="text-align: center;">{{ $detail['quantity'] }}</td>
                            <td style="text-align: right;">{{ $symbol }}{{ $detail['price'] }} </td>
                            <td style="text-align: right;">
                                {{ $symbol }}{{ $detail['quantity'] * $detail['price'] }} </td>
                            <td style="text-align: center;">{{ $detail['TaxNet'] }}% </td>
                            <td style="text-align: right;">
                                {{ $symbol }}{{ $detail['quantity'] * $detail['taxe'] }} </td>
                            <td style="text-align: right;">{{ $symbol }}{{ $detail['total'] }} </td>
                        </tr>
                        <?php
                        $qtyTotal += $detail['quantity'];
                        $exSTTotal += $detail['quantity'] * $detail['price'];
                        $stTotal += $detail['quantity'] * $detail['taxe'];
                        $totalT += $detail['total'];
                        ?>
                    @endforeach
                    <tr style="font-weight:bold; font-size:12px;">
                        <td></td>
                        <td>TOTAL</td>
                        <td style="text-align: center;">{{ $qtyTotal }}</td>
                        <td> </td>
                        <td style="text-align: right;">{{ $symbol }}{{ $exSTTotal }} </td>
                        <td> </td>
                        <td style="text-align: right;">{{ $symbol }}{{ $stTotal }} </td>
                        <td style="text-align: right;">{{ $symbol }}{{ $totalT }} </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="total">
            <table>
                <tr>
                    {{-- <td>Order Tax</td>
                    <td>{{ $sale['TaxNet'] }} </td> --}}
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    {{-- <td>Discount</td>
                    <td>{{ $sale['discount'] }} </td> --}}
                </tr>
                <tr>
                    {{-- <td>Shipping</td>
                    <td>{{ $sale['shipping'] }} </td> --}}
                </tr>
                <tr>
                    {{-- <td>Total</td>
                    <td>{{ $symbol }} {{ $sale['GrandTotal'] }} </td> --}}
                </tr>

                <tr>
                    {{-- <td>Paid Amount</td>
                    <td>{{ $symbol }} {{ $sale['paid_amount'] }} </td> --}}
                </tr>

                <tr>
                    {{-- <td>Paid Amount</td>
                    <td>{{ $symbol }} {{ $sale['paid_amount'] }} </td> --}}
                </tr>

                <tr>
                    {{-- <td>Paid Amount</td>
                    <td>{{ $symbol }} {{ $sale['paid_amount'] }} </td> --}}
                </tr>

                <tr>
                    {{-- <td>Due</td>
                    <td>{{ $symbol }} {{ $sale['due'] }} </td> --}}
                    <td>Signature:</td>
                    <td>_________________________</td>
                </tr>
            </table>
        </div>
        <div id="signature">
            @if ($setting['is_invoice_footer'] && $setting['invoice_footer'] !== null)
                <p>{{ $setting['invoice_footer'] }}</p>
            @endif
        </div>
    </main>
</body>

</html>
