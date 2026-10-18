<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $metadata->title ?? 'Hata')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #E66239;
            --primary-hover: #D04F26;
            --bg: #FFFFFF;
            --text-heading: #0F172A;
            --text-muted: #64748B;
            --btn-sec-bg: #F8FAFC;
            --btn-sec-text: #334155;
            --btn-sec-border: #E2E8F0;
            --btn-sec-hover-bg: #F1F5F9;
            --btn-sec-hover-border: #CBD5E1;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg);
            color: var(--text-heading);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
            -webkit-font-smoothing: antialiased;
        }

        .error-wrapper {
            max-width: 480px;
            margin: 0 auto;
        }

        .error-code {
            font-family: 'Outfit', sans-serif;
            font-size: 112px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -3px;
            color: var(--primary);
            margin-bottom: 20px;
            user-select: none;
        }

        @media (max-width: 576px) {
            .error-code {
                font-size: 88px;
            }
        }

        .error-title {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--text-heading);
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .error-message {
            font-size: 15px;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 32px;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 28px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
            box-shadow: 0 1px 2px rgba(230, 98, 57, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(230, 98, 57, 0.25);
        }

        .btn-secondary {
            background-color: var(--btn-sec-bg);
            color: var(--btn-sec-text);
            border-color: var(--btn-sec-border);
        }

        .btn-secondary:hover {
            background-color: var(--btn-sec-hover-bg);
            color: var(--text-heading);
            border-color: var(--btn-sec-hover-border);
            transform: translateY(-1px);
        }

        .error-reference {
            margin-top: 36px;
            font-size: 12px;
            color: #94A3B8;
            font-family: ui-monospace, SFMono-Regular, monospace;
        }
    </style>
</head>
<body>

    <div class="error-wrapper">
        <div class="error-code">
            {{ $metadata->statusCode ?? 500 }}
        </div>

        <h1 class="error-title">
            @yield('heading', $metadata->title ?? 'Bir Hata Oluştu')
        </h1>
        
        <p class="error-message">
            @yield('message', $metadata->description ?? 'İşleminiz gerçekleştirilemedi.')
        </p>

        <div class="action-buttons">
            @php
                $pUrl = $metadata->primaryActionUrl ?? 'javascript:smartGoBack()';
                $pLabel = $metadata->primaryActionLabel ?? 'Yönetim Paneline Dön';
                $pType = $metadata->primaryActionType ?? 'link';
            @endphp

            @if($pType === 'back')
                <button type="button" class="btn btn-primary" onclick="smartGoBack()">
                    {{ $pLabel }}
                </button>
            @elseif($pType === 'reload')
                <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                    {{ $pLabel }}
                </button>
            @else
                <a href="{{ $pUrl }}" class="btn btn-primary">
                    {{ $pLabel }}
                </a>
            @endif

            @if(!empty($metadata->secondaryActionLabel) && !empty($metadata->secondaryActionUrl))
                @if($metadata->secondaryActionUrl === 'javascript:smartGoBack()')
                    <button type="button" class="btn btn-secondary" onclick="smartGoBack()">
                        {{ $metadata->secondaryActionLabel }}
                    </button>
                @else
                    <a href="{{ $metadata->secondaryActionUrl }}" class="btn btn-secondary">
                        {{ $metadata->secondaryActionLabel }}
                    </a>
                @endif
            @endif
        </div>

        @if(!empty($referenceId ?? $metadata->referenceId))
            <div class="error-reference">
                Referans: {{ $referenceId ?? $metadata->referenceId }}
            </div>
        @endif
    </div>

    <script nonce="{{ $cspNonce }}">
        function smartGoBack(fallback = '{{ route("dashboard", [], false) ?: "/" }}') {
            try {
                if (document.referrer && document.referrer.indexOf(window.location.host) !== -1 && window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = fallback;
                }
            } catch (e) {
                window.location.href = fallback;
            }
        }
    </script>
</body>
</html>
