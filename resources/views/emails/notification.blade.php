<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subjectText }}</title>
</head>
<body style="margin: 0; padding: 0; background: #f3f6f4; font-family: Arial, sans-serif; color: #1f2933;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f3f6f4; padding: 32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 560px; background: #ffffff; border: 1px solid #dbe7df; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="padding: 22px 26px; background: #17643b; color: #ffffff;">
                            <div style="font-size: 18px; font-weight: 700;">{{ $appName }}</div>
                            <div style="font-size: 13px; margin-top: 4px; opacity: .9;">Nouvelle notification</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 26px;">
                            <p style="margin: 0 0 14px; font-size: 15px;">Bonjour {{ $receiverName }},</p>
                            <p style="margin: 0 0 16px; font-size: 15px;">
                                Vous avez recu une notification de <strong>{{ $senderName }}</strong>.
                            </p>

                            <div style="border-left: 4px solid #2f8f59; padding: 12px 14px; background: #f7fbf8; margin: 0 0 22px;">
                                <div style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">{{ $subjectText }}</div>
                                <div style="font-size: 14px; line-height: 1.55; color: #334155;">{{ $bodyText }}</div>
                            </div>

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 0 22px;">
                                <tr>
                                    <td style="background: #198754; border-radius: 6px;">
                                        <a href="{{ $messageUrl }}" style="display: inline-block; padding: 12px 18px; color: #ffffff; font-size: 14px; font-weight: 700; text-decoration: none;">
                                            Ouvrir dans l'application
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 1.5;">
                                Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
                                <a href="{{ $messageUrl }}" style="color: #17643b;">{{ $messageUrl }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
