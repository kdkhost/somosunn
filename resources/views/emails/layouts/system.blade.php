<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: Arial, Helvetica, sans-serif;">
    @php
        $siteName = $siteName ?? (string) (App\Models\Setting::get('app_name') ?: App\Models\Setting::get('company_name') ?: config('app.name', 'UNN'));
        $logoUrl = $logoUrl ?? (function () {
            $logo = App\Models\Setting::get('logo_admin') ?: App\Models\Setting::get('logo_front') ?: App\Models\Setting::get('logo_image');
            return $logo ? asset(ltrim((string) $logo, '/')) : asset('img/logo.svg');
        })();
        $primaryColor = $primaryColor ?? (string) (App\Models\Setting::get('site_color_primary') ?: '#1F5EDB');
        $secondaryColor = $secondaryColor ?? (string) (App\Models\Setting::get('site_color_secondary') ?: '#177FD6');
        $year = date('Y');
    @endphp

    <div style="background-color: #f4f6f9; padding: 20px; min-height: 100%;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td align="center">
                    <div style="background-color: #ffffff; max-width: 600px; padding: 0px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;">
                        <div style="background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%); padding: 30px 20px; text-align: center;">
                            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="max-height: 60px; max-width: 200px;">
                        </div>

                        <div style="padding: 30px; color: #333333; line-height: 1.6;">
                            @yield('content')
                        </div>

                        <div style="background-color: #f8f9fa; padding: 20px; text-align: center; color: #777777; font-size: 12px; border-top: 1px solid #eeeeee;">
                            <p style="margin: 0;">&copy; {{ $year }} {{ $siteName }}. Todos os direitos reservados.</p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

