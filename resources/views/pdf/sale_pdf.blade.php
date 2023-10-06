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
            <div><strong> Date : </strong>{{ $sale['date'] }}</div>
            <div><strong> PO Number : </strong> {{ $sale['po_number'] }}</div>
            <div><strong> Status : </strong> {{ $sale['statut'] }}</div>
            <div><strong> Payment Status : </strong> {{ $sale['payment_status'] }}</div>
        </div>
        <div id="Title-heading">
            BILL
        </div>
    </header>
    <header class="clearfix">
        <div id="Title-heading-company">
            {{ $setting['CompanyName'] }}
            <span class="company-address">{{ $setting['CompanyAdress'] }}</span>
        </div>
    </header>
    <main>
        <div id="details" class="clearfix">
            <div id="invoice">
                <table class="table-sm">
                    <thead>
                        <tr>
                            <th class="desc">Invoice No :</strong> {{ $sale['Ref'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                {{-- <div><strong>Invoice No :</strong> {{ $sale['Ref'] }}</div> --}}
                                <div><strong>Sales Tax # :</strong> 12314{{ $setting['company_st'] }}</div>
                                <div><strong>N.T.N :</strong> 12323{{ $setting['company_tax'] }}</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="client">
                <table class="table-sm">
                    <thead>
                        <tr>
                            <th class="desc">Customer Information</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div><strong>Full Name :</strong> {{ $sale['client_name'] }}</div>
                                <div><strong>Phone :</strong> {{ $sale['client_phone'] }}</div>
                                <div><strong>Email :</strong> {{ $sale['client_email'] }}</div>
                                <div><strong>Address :</strong> {{ $sale['client_adr'] }}</div>
                                @if ($sale['client_tax'])
                                    <div><strong>N.T.N :</strong> {{ $sale['client_tax'] }}</div>
                                @endif
                                @if ($sale['sales_tax_number'])
                                    <div><strong>ST Registration No. :</strong> {{ $sale['sales_tax_number'] }}</div>
                                @endif
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
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>DISCOUNT</th>
                        <th>ST Value</th>
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($details as $detail)
                        <tr>
                            <td>
                                <span>{{ $detail['code'] }} ({{ $detail['name'] }})</span>
                                @if ($detail['is_imei'] && $detail['imei_number'] !== null)
                                    <p>IMEI/SN : {{ $detail['imei_number'] }}</p>
                                @endif
                            </td>
                            <td>{{ $detail['quantity'] }}/{{ $detail['unitSale'] }}</td>
                            <td>{{ $detail['price'] }} </td>
                            <td>{{ $detail['DiscountNet'] }} </td>
                            <td>{{ $detail['taxe'] }} </td>
                            <td>{{ $detail['total'] }} </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div id="total">
            <table>
                <tr>
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
