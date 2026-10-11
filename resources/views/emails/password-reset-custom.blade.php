<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - CCTV Pertamina</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f4f4f4">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="margin-top: 30px; border-radius: 8px; overflow: hidden;">
                    
                    <!-- Header (no logo) -->
                    <tr>
                        <td align="center" bgcolor="#003366" style="padding: 20px;">
                            <h2 style="color: #ffffff; margin: 10px 0 0 0; font-size: 20px;">CCTV Kilang Pertamina Internasional RU VI</h2>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px;">
                            <div style="text-align:center; margin:20px 0;">
                                @if(!empty($bodyImageCid ?? null))
                                    <img src="cid:{{ $bodyImageCid }}" alt="Logo Info" style="max-width:100%; height:auto;">
                                @endif
                            </div>
                            <h3 style="color: #111111; font-size: 20px; margin-top: 0;">Reset Kata Sandi Anda</h3>

                            <p style="color: #333333; font-size: 16px; line-height: 1.6;">
                                Kami menerima permintaan untuk mengatur ulang kata sandi Anda di sistem
                                <strong>CCTV Kilang Pertamina Internasional RU VI Balongan</strong>.
                            </p>

                            <p style="color: #333333; font-size: 16px;">Silakan klik tombol di bawah ini untuk melanjutkan:</p>

                            <div style="margin: 30px 0; text-align: center;">
                                <a href="{{ $actionUrl }}"
                                   style="background-color: #0d6efd; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; display: inline-block; font-weight: bold;">
                                    Reset Password
                                </a>
                            </div>

                            <p style="color: #999999; font-size: 14px;">
                                Tautan ini hanya berlaku selama <strong>60 menit</strong>. Jika Anda tidak meminta pengaturan ulang, abaikan email ini.
                            </p>

                            <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">

                            <p style="color: #555555; font-size: 14px; text-align: center;">
                                Salam Hormat, <br>
                                <strong>Admin ICT CCTV Kilang Pertamina RU VI</strong>
                            </p>

                            <p style="color: #aaaaaa; font-size: 12px; text-align: center;">
                                PT. Pertamina Patra Niaga Refinery Unit Balongan<br>
                                Jl. Raya Balongan, Indramayu, Jawa Barat 45217
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td bgcolor="#f4f4f4" style="text-align:center; padding:20px; font-size:12px; color:#aaaaaa;">
                            © {{ date('Y') }} Pertamina RU VI. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
