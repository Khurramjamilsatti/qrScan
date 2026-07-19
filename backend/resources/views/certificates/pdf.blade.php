<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; size: A4 landscape; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            font-family: {{ $pdfFontFamily }};
            color: {{ $textColor }};
            background: {{ $backgroundColor }};
        }

        .page-shell {
            padding: 2mm;
            background: {{ $backgroundColor }};
        }
        .frame-outer {
            border: 1.5pt solid {{ $goldColor }};
            padding: 1.5mm;
            background: {{ $backgroundColor }};
        }
        .frame-mid {
            border: 0.75pt solid {{ $goldColor }};
            padding: 2mm;
            background: {{ $backgroundColor }};
        }
        .frame-inner {
            position: relative;
            background: {{ $backgroundColor }};
            border: 0.5pt solid #e8e4dc;
            min-height: 196mm;
        }

        .body-content {
            padding: 9mm 12mm 0;
            text-align: center;
        }

        .footer-wrap {
            position: absolute;
            bottom: 5mm;
            left: 12mm;
            right: 12mm;
            text-align: center;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5mm;
        }
        .header-table td { vertical-align: middle; }
        .header-side { width: 32mm; text-align: center; }
        .header-center { text-align: center; }

        .avatar-ring {
            width: 26mm;
            height: 26mm;
            border: 1.5pt solid {{ $goldColor }};
            border-radius: 13mm;
            background: #fff;
            margin: 0 auto;
            text-align: center;
            line-height: 26mm;
        }
        .avatar-ring img {
            width: 24mm;
            height: 24mm;
            vertical-align: middle;
        }

        .issuer {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 2pt;
            text-transform: uppercase;
            color: {{ $mutedColor }};
            margin-bottom: 2.5mm;
        }

        .cert-heading {
            font-family: {{ $pdfFontFamily }};
            font-size: 22pt;
            font-weight: bold;
            letter-spacing: 3.5pt;
            text-transform: uppercase;
            color: {{ $textColor }};
            line-height: 1.2;
            margin-bottom: 1.5mm;
        }

        .sub-heading {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 1.8pt;
            text-transform: uppercase;
            color: {{ $goldColor }};
        }

        .divider {
            width: 80mm;
            margin: 4mm auto 5mm;
            border-collapse: collapse;
        }
        .divider td { padding: 0; vertical-align: middle; }
        .divider-line { height: 0.75pt; background: {{ $goldColor }}; }
        .divider-dot {
            width: 10mm;
            text-align: center;
            font-size: 8pt;
            color: {{ $goldColor }};
            line-height: 1;
        }

        .presented {
            font-family: {{ $pdfFontFamily }};
            font-size: 12pt;
            font-style: italic;
            color: {{ $mutedColor }};
            margin-bottom: 4mm;
        }

        .recipient {
            font-family: {{ $pdfFontFamily }};
            font-size: 34pt;
            font-weight: bold;
            color: {{ $textColor }};
            line-height: 1.1;
            margin-bottom: 3mm;
            padding-bottom: 3mm;
            border-bottom: 0.75pt solid {{ $ruleLineColor }};
        }

        .award-lead {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            color: {{ $mutedColor }};
            margin: 4mm 0 2mm;
        }

        .award {
            font-family: {{ $pdfFontFamily }};
            font-size: 16pt;
            font-weight: bold;
            color: {{ $textColor }};
            margin-bottom: 3mm;
        }

        .desc {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: {{ $mutedColor }};
            line-height: 1.6;
            max-width: 200mm;
            margin: 0 auto 4mm;
        }

        .dates-row {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5pt;
            color: {{ $mutedColor }};
            margin-bottom: 2mm;
        }
        .dates-row strong { color: {{ $textColor }}; font-weight: bold; }

        .footer {
            padding-top: 4mm;
            border-top: 0.75pt solid {{ $footerBorderColor }};
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .footer-table td {
            width: 33.33%;
            vertical-align: bottom;
            text-align: center;
            padding: 0 4mm;
        }

        .sig-area { height: 18mm; text-align: center; }
        .sig-img { max-height: 16mm; max-width: 48mm; }
        .sig-caption {
            font-family: DejaVu Sans, sans-serif;
            font-size: 6.5pt;
            font-weight: bold;
            letter-spacing: 0.8pt;
            text-transform: uppercase;
            color: #7a7288;
            border-top: 0.75pt solid {{ $sigBorderColor }};
            padding-top: 1.5mm;
            margin-top: 1mm;
        }

        .qr-box {
            display: inline-block;
            padding: 1.5mm;
            background: #fff;
            border: 0.75pt solid #e0dcd4;
        }
        .qr-img { width: 22mm; height: 22mm; display: block; }
        .qr-caption {
            font-family: DejaVu Sans, sans-serif;
            font-size: 6pt;
            font-weight: bold;
            letter-spacing: 0.6pt;
            text-transform: uppercase;
            color: #7a7288;
            margin-top: 1.2mm;
        }

        .cert-id {
            margin-top: 3mm;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 6.5pt;
            letter-spacing: 1pt;
            color: #9a92a8;
        }

        .tpl-elegant .cert-heading { font-weight: normal; font-size: 18pt; letter-spacing: 5pt; }
        .tpl-elegant .recipient { font-weight: normal; font-style: italic; font-size: 30pt; }
        .tpl-modern .cert-heading { font-family: DejaVu Sans, sans-serif; letter-spacing: 1pt; }
        .tpl-modern .recipient { font-family: DejaVu Sans, sans-serif; font-style: normal; }
    </style>
</head>
<body class="tpl-{{ $cert->template ?? 'classic' }}">
    @php
        $showDates = ($cert->settings['show_dates'] ?? true) !== false;
        $showCertId = ($cert->settings['show_certificate_id'] ?? true) !== false;
        $showQr = ($cert->settings['show_qr'] ?? true) !== false;
    @endphp

    <div class="page-shell">
        <div class="frame-outer">
            <div class="frame-mid">
                <div class="frame-inner">
                    <div class="body-content">
                        <table class="header-table">
                            <tr>
                                <td class="header-side">
                                    @if($logo)
                                        <div class="avatar-ring">
                                            <img src="{{ $logo }}" alt="">
                                        </div>
                                    @endif
                                </td>
                                <td class="header-center">
                                    @if($cert->issuer_name)
                                        <div class="issuer">{{ $cert->issuer_name }}</div>
                                    @endif
                                    <div class="cert-heading">{{ $cert->title }}</div>
                                    <div class="sub-heading">Award of Achievement</div>
                                </td>
                                <td class="header-side">
                                    @if($seal)
                                        <div class="avatar-ring">
                                            <img src="{{ $seal }}" alt="">
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <table class="divider" align="center">
                            <tr>
                                <td class="divider-line" style="width:42%"></td>
                                <td class="divider-dot">&#9670;</td>
                                <td class="divider-line" style="width:42%"></td>
                            </tr>
                        </table>

                        <div class="presented">This is to certify that</div>
                        <div class="recipient">{{ $cert->recipient_name }}</div>

                        @if($cert->award_title)
                            <div class="award-lead">has successfully completed</div>
                            <div class="award">{{ $cert->award_title }}</div>
                        @endif

                        @if($cert->description)
                            <div class="desc">{{ $cert->description }}</div>
                        @endif

                        @if($showDates && ($cert->completion_date || $cert->issue_date))
                            <div class="dates-row">
                                @if($cert->completion_date)
                                    Completed on <strong>{{ $cert->completion_date->format('F j, Y') }}</strong>
                                @endif
                                @if($cert->completion_date && $cert->issue_date)
                                    &nbsp;&nbsp;&#8226;&nbsp;&nbsp;
                                @endif
                                @if($cert->issue_date)
                                    Issued on <strong>{{ $cert->issue_date->format('F j, Y') }}</strong>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="footer-wrap">
                        <div class="footer">
                            <table class="footer-table">
                                <tr>
                                    <td>
                                        <div class="sig-area">
                                            @if($instructorSig)
                                                <img src="{{ $instructorSig }}" class="sig-img" alt="">
                                            @endif
                                        </div>
                                        <div class="sig-caption">Instructor Signature</div>
                                    </td>
                                    <td>
                                        @if($showQr && $qrPath)
                                            <div class="qr-box">
                                                <img src="{{ $qrPath }}" class="qr-img" alt="">
                                            </div>
                                        @endif
                                        <div class="qr-caption">Verify at scan</div>
                                    </td>
                                    <td>
                                        <div class="sig-area">
                                            @if($orgSig)
                                                <img src="{{ $orgSig }}" class="sig-img" alt="">
                                            @endif
                                        </div>
                                        <div class="sig-caption">Authorized Signature</div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        @if($showCertId)
                            <div class="cert-id">{{ $cert->certificate_id }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
