<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #0f0f1a; color: #e2e8f0; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #1a1a2e; border-radius: 16px; overflow: hidden; border: 1px solid #4a3728; }
        .header { background: linear-gradient(135deg, #6B21A8, #92400E); padding: 40px; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 28px; }
        .header p { color: rgba(255,255,255,0.8); margin: 8px 0 0; }
        .body { padding: 40px; }
        .body p { color: #cbd5e1; line-height: 1.6; margin: 0 0 16px; }
        .quest-box { background: #2d1f3d; border: 1px solid #6B21A8; border-radius: 12px; padding: 20px; margin: 24px 0; }
        .quest-box h3 { color: #c084fc; margin: 0 0 8px; }
        .quest-box p { color: #a78bfa; margin: 0; font-size: 14px; }
        .btn { display: inline-block; background: #6B21A8; color: white; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-weight: bold; font-size: 16px; margin: 8px 4px; }
        .btn-secondary { background: #92400E; }
        .buttons { text-align: center; margin: 32px 0; }
        .footer { text-align: center; padding: 20px 40px; border-top: 1px solid #2d1f3d; }
        .footer p { color: #64748b; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎲 QuestMaster</h1>
            <p>Ton Maître de Jeu IA Personnel</p>
        </div>
        <div class="body">
            <p>Salut aventurier ! 👋</p>
            <p>
                <strong style="color: #c084fc;">{{ $inviter->name }}</strong>
                t'invite à rejoindre une quête épique sur QuestMaster !
            </p>

            <div class="quest-box">
                <h3>⚔️ La quête t'attend :</h3>
                <p>{{ $conversation->title }}</p>
            </div>

            <p>Pour rejoindre cette aventure, tu dois d'abord créer ton compte QuestMaster gratuitement.</p>

            <div class="buttons">
                <a href="{{ url('/register') }}" class="btn">
                    🎲 Créer mon compte
                </a>
                <a href="{{ url('/login') }}" class="btn btn-secondary">
                    ⚔️ Se connecter
                </a>
            </div>

            <p style="font-size: 14px; color: #64748b;">
                Une fois inscrit, demande à {{ $inviter->name }} de t'inviter avec ton adresse email.
            </p>
        </div>
        <div class="footer">
            <p>QuestMaster — Propulsé par OpenRouter 🚀</p>
            <p>Tu reçois cet email car {{ $inviter->name }} t'a invité.</p>
        </div>
    </div>
</body>
</html>
