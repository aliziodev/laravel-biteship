<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Pengiriman — {{ $label->waybill_id ?? 'N/A' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
        }

        .label {
            width: 100mm;
            min-height: 150mm;
            border: 2px solid #000;
            padding: 4mm;
            page-break-inside: avoid;
        }

        /* Header — nama kurir */
        .label-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }

        .label-header .courier-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .label-header .courier-type {
            font-size: 11px;
            color: #444;
            text-transform: uppercase;
        }

        /* Waybill / Resi */
        .waybill-section {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }

        .waybill-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }

        .waybill-number {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            word-break: break-all;
        }

        /* COD badge */
        .cod-badge {
            display: inline-block;
            background: #000;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 1mm 3mm;
            margin-top: 2mm;
            text-transform: uppercase;
        }

        /* Alamat section */
        .address-section {
            margin-bottom: 3mm;
        }

        .address-block {
            border: 1px solid #ccc;
            padding: 2mm 3mm;
            margin-bottom: 2mm;
        }

        .address-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 1mm;
        }

        .address-name {
            font-size: 13px;
            font-weight: bold;
        }

        .address-phone {
            font-size: 11px;
        }

        .address-detail {
            font-size: 11px;
            margin-top: 1mm;
            color: #333;
        }

        /* Recipient highlight */
        .recipient .address-block {
            border: 2px solid #000;
        }

        /* Items summary */
        .items-section {
            border-top: 1px dashed #000;
            padding-top: 2mm;
            margin-top: 2mm;
        }

        .items-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 1mm;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            padding: 0.5mm 0;
            border-bottom: 1px dotted #ddd;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        /* Footer */
        .label-footer {
            border-top: 1px solid #000;
            margin-top: 3mm;
            padding-top: 2mm;
            font-size: 9px;
            display: flex;
            justify-content: space-between;
            color: #555;
        }

        @media print {
            body { margin: 0; }
            .label { border: 2px solid #000; }
        }
    </style>
</head>
<body>

<div class="label">

    {{-- Header: Nama Kurir --}}
    <div class="label-header">
        <div class="courier-name">{{ strtoupper($label->courier_name) }}</div>
        <div class="courier-type">{{ strtoupper($label->courier_type) }}</div>
    </div>

    {{-- Nomor Resi / Waybill --}}
    <div class="waybill-section">
        <div class="waybill-label">No. Resi</div>
        <div class="waybill-number">{{ $label->waybill_id ?? 'Menunggu Pickup' }}</div>

        @if($label->isCod())
            <span class="cod-badge">COD — Rp {{ number_format($label->cod_amount, 0, ',', '.') }}</span>
        @endif
    </div>

    {{-- Alamat Penerima --}}
    <div class="address-section recipient">
        <div class="address-block">
            <div class="address-label">Penerima</div>
            <div class="address-name">{{ $label->recipient_name }}</div>
            <div class="address-phone">{{ $label->recipient_phone }}</div>
            <div class="address-detail">{{ $label->recipient_address }}</div>
        </div>
    </div>

    {{-- Alamat Pengirim --}}
    <div class="address-section">
        <div class="address-block">
            <div class="address-label">Pengirim</div>
            <div class="address-name">{{ $label->sender_name }}</div>
            <div class="address-phone">{{ $label->sender_phone }}</div>
            <div class="address-detail">{{ $label->sender_address }}</div>
        </div>
    </div>

    {{-- Daftar Item --}}
    @if(count($label->items) > 0)
    <div class="items-section">
        <div class="items-label">Isi Paket</div>
        @foreach($label->items as $item)
        <div class="item-row">
            <span>{{ $item['name'] ?? '-' }} @if(($item['quantity'] ?? 1) > 1)(x{{ $item['quantity'] }})@endif</span>
            <span>{{ number_format(($item['weight'] ?? 0) * ($item['quantity'] ?? 1)) }}g</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Footer --}}
    <div class="label-footer">
        <span>Total Berat: {{ number_format($label->total_weight) }}g</span>
        @if($label->tracking_id)
            <span>Tracking: {{ $label->tracking_id }}</span>
        @endif
    </div>

</div>

</body>
</html>
