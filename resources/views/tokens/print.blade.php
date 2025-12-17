<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token {{ $token->token_number }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        
        body {
            width: 80mm;
            margin: 0;
            padding: 10mm;
            font-family: 'Courier New', monospace;
            font-size: 12pt;
        }
        
        .receipt {
            text-align: center;
        }
        
        .logo {
            max-width: 60mm;
            margin-bottom: 5mm;
        }
        
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 3mm;
        }
        
        .divider {
            border-top: 2px dashed #000;
            margin: 5mm 0;
        }
        
        .token-number {
            font-size: 36pt;
            font-weight: bold;
            margin: 5mm 0;
            letter-spacing: 3px;
        }
        
        .vip-badge {
            background: gold;
            color: black;
            padding: 2mm 5mm;
            font-size: 14pt;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 3mm;
        }
        
        .info-row {
            margin: 3mm 0;
            font-size: 11pt;
        }
        
        .label {
            font-weight: bold;
        }
        
        .qrcode {
            margin: 5mm 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .qrcode img {
            max-width: 50mm !important;
            height: auto !important;
        }
        
        .footer {
            font-size: 9pt;
            margin-top: 5mm;
            color: #666;
        }
        
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Company Logo (placeholder - replace with actual logo) -->
        <div class="company-name">QUEUE MANAGEMENT SYSTEM</div>
        
        <div class="divider"></div>
        
        @if($token->is_vip)
            <div class="vip-badge">★ VIP ★</div>
        @endif
        
        <div class="token-number">{{ $token->token_number }}</div>
        
        <div class="divider"></div>
        
        <div class="info-row">
            <span class="label">Service:</span> {{ $token->service->name }}
        </div>
        
        <div class="info-row">
            <span class="label">Department:</span> {{ $token->service->department->name }}
        </div>
        
        <div class="info-row">
            <span class="label">Date & Time:</span><br>
            {{ $token->issued_at->format('d/m/Y h:i A') }}
        </div>
        
        <div class="info-row">
            <span class="label">Waiting Ahead:</span> {{ $waitingCount }} customers
        </div>
        
        <div class="divider"></div>
        
        <!-- QR Code -->
        <div class="qrcode">
            <div id="qrcode"></div>
        </div>
        
        <div class="footer">
            Scan QR code to check status<br>
            Thank you for your patience!
        </div>
    </div>

    <!-- QR Code Library -->
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            // Generate QR code with URL to status page
            const statusUrl = "{{ url('/token/' . $token->id . '/status') }}";
            
            // Wait a bit for library to load
            setTimeout(function() {
                try {
                    const qrcode = new QRCode(document.getElementById("qrcode"), {
                        text: statusUrl,
                        width: 200,
                        height: 200,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.H
                    });
                    
                    console.log('QR code generated successfully!');
                    
                    // Auto-print after QR code is generated
                    setTimeout(() => {
                        window.print();
                        
                        // Redirect to dashboard after print dialog
                        setTimeout(() => {
                            window.location.href = "{{ route('dashboard') }}";
                        }, 1000);
                    }, 1000);
                } catch (error) {
                    console.error('QR code error:', error);
                    // Print anyway even if QR fails
                    setTimeout(() => {
                        window.print();
                        
                        // Redirect to dashboard after print dialog
                        setTimeout(() => {
                            window.location.href = "{{ route('dashboard') }}";
                        }, 1000);
                    }, 500);
                }
            }, 500);
        });
    </script>
</body>
</html>
