<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Conversation;
use App\Models\ConversationMember;
use App\Models\CustomInstruction;
use App\Models\Message;
use App\Models\User;
use App\Models\UserModelUsage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Créer le compte Professeur ──
        $prof = User::where('email', 'Prof@ifosup.be')->first();
        if (!$prof) {
            $prof = User::create([
                'name'              => 'Professeur IFOSUP',
                'email'             => 'Prof@ifosup.be',
                'password'          => Hash::make('Ifosup'),
                'email_verified_at' => now(),
            ]);
        }

        // ── Créer le compte Aventurier ──
        $aventurier = User::where('email', 'Aventurier@questmaster.be')->first();
        if (!$aventurier) {
            $aventurier = User::create([
                'name'              => 'Aventurier Demo',
                'email'             => 'Aventurier@questmaster.be',
                'password'          => Hash::make('Quest1234'),
                'email_verified_at' => now(),
            ]);
        }


        // ── Instructions personnalisées pour le prof ──
        CustomInstruction::firstOrCreate(
            ['user_id' => $prof->id],
            [
                'persona'        => "Tu es un assistant pedagogique specialise en developpement web Laravel et Vue.js.",
                'context'        => "Je suis un professeur en informatique qui evalue un projet etudiant de clone ChatGPT.",
                'response_style' => "Reponds de facon precise et technique. Utilise des exemples concrets.",
                'language'       => 'fr',
                'is_active'      => true,
            ]
        );

        // ── Conversation 1 : Demo streaming (prof) ──
        $conv1 = Conversation::where('title', 'Demo du streaming SSE en temps reel')
            ->where('user_id', $prof->id)
            ->first();

        if (!$conv1) {
            $conv1 = Conversation::create([
                'user_id'      => $prof->id,
                'title'        => 'Demo du streaming SSE en temps reel',
                'model'        => 'openai/gpt-4o-mini',
                'total_tokens' => 450,
            ]);

            Message::insert([
                [
                    'conversation_id' => $conv1->id,
                    'role'            => 'user',
                    'content'         => "Peux-tu me montrer comment fonctionne le streaming dans cette application ?",
                    'tokens_used'     => 18,
                    'model_used'      => null,
                    'created_at'      => now()->subMinutes(10),
                    'updated_at'      => now()->subMinutes(10),
                ],
                [
                    'conversation_id' => $conv1->id,
                    'role'            => 'assistant',
                    'content'         => "Bien sur ! Le streaming fonctionne grace aux Server-Sent Events (SSE).\n\nQuand tu envoies un message :\n1. Laravel ouvre une connexion stream vers OpenRouter\n2. Chaque token recu est immediatement transmis au navigateur\n3. Vue.js lit le flux et affiche les mots au fur et a mesure\n\nC est pourquoi tu vois les mots apparaitre un par un, comme si l IA tapait en direct !\n\nQue veux-tu tester ensuite ?\n\n1. **Changer de modele LLM** et comparer les reponses\n2. **Envoyer une image** pour tester le mode multimodal\n3. **Utiliser un agent** preconfigure",
                    'tokens_used'     => 132,
                    'model_used'      => 'openai/gpt-4o-mini',
                    'created_at'      => now()->subMinutes(9),
                    'updated_at'      => now()->subMinutes(9),
                ],
                [
                    'conversation_id' => $conv1->id,
                    'role'            => 'user',
                    'content'         => "Tres interessant ! Et comment sont sauvegardees les conversations ?",
                    'tokens_used'     => 14,
                    'model_used'      => null,
                    'created_at'      => now()->subMinutes(8),
                    'updated_at'      => now()->subMinutes(8),
                ],
                [
                    'conversation_id' => $conv1->id,
                    'role'            => 'assistant',
                    'content'         => "Chaque conversation est sauvegardee en base de donnees MySQL !\n\nVoici la structure :\n- **conversations** : le titre genere automatiquement, le modele LLM choisi\n- **messages** : chaque echange avec son role (user/assistant) et le nombre de tokens\n- **message_files** : les images jointes\n\nLe titre est genere automatiquement au **premier message** via un appel separe a GPT-4o Mini.\n\nQue veux-tu explorer ?\n\n1. **Voir le systeme d agents** personnalises\n2. **Tester le partage** de conversation\n3. **Configurer les instructions** personnalisees",
                    'tokens_used'     => 148,
                    'model_used'      => 'openai/gpt-4o-mini',
                    'created_at'      => now()->subMinutes(7),
                    'updated_at'      => now()->subMinutes(7),
                ],
            ]);
        }

        // ── Conversation 2 : Aventure JDR avec dés (prof) ──
        $conv2 = Conversation::where('title', 'Aventure dans le Donjon des Ombres')
            ->where('user_id', $prof->id)
            ->first();

        if (!$conv2) {
            $conv2 = Conversation::create([
                'user_id'      => $prof->id,
                'title'        => 'Aventure dans le Donjon des Ombres',
                'model'        => 'openai/gpt-4o-mini',
                'total_tokens' => 620,
            ]);

            Message::insert([
                [
                    'conversation_id' => $conv2->id,
                    'role'            => 'user',
                    'content'         => "Je veux commencer une aventure dans un donjon sombre et mysterieux.",
                    'tokens_used'     => 16,
                    'model_used'      => null,
                    'created_at'      => now()->subMinutes(20),
                    'updated_at'      => now()->subMinutes(20),
                ],
                [
                    'conversation_id' => $conv2->id,
                    'role'            => 'assistant',
                    'content'         => "**Bienvenue dans le Donjon des Ombres !**\n\nTu te tiens devant une lourde porte de pierre couverte de runes anciennes. L air est froid et une legere odeur de soufre emane des profondeurs. Ta torche vacille dans le courant d air mysterieux.\n\nAu loin, tu entends un grognement. Ou peut-etre est-ce le vent ?\n\nQue fais-tu ?\n\n1. **Pousser la porte** et entrer prudemment, epee a la main\n2. **Examiner les runes** pour chercher des indices\n3. **Attendre et ecouter** pour mieux cerner les dangers",
                    'tokens_used'     => 178,
                    'model_used'      => 'openai/gpt-4o-mini',
                    'created_at'      => now()->subMinutes(19),
                    'updated_at'      => now()->subMinutes(19),
                ],
                [
                    'conversation_id' => $conv2->id,
                    'role'            => 'user',
                    'content'         => "/d20 -- je tente d examiner les runes !",
                    'tokens_used'     => 15,
                    'model_used'      => null,
                    'created_at'      => now()->subMinutes(18),
                    'updated_at'      => now()->subMinutes(18),
                ],
                [
                    'conversation_id' => $conv2->id,
                    'role'            => 'assistant',
                    'content'         => "**Lancer 1d20 = 17** -- Excellent resultat !\n\nAvec une telle perspicacite, tu dechiffres les runes avec facilite !\n\nLes symboles anciens te revelent un avertissement : *Celui qui porte de la lumiere sera protege des creatures des tenebres*. Plus bas : *frotter les runes avec du sang permettra d ouvrir un passage secret*.\n\nDans un coin, tu remarques aussi une petite alcove cachee.\n\nQue fais-tu ?\n\n1. **Entrer par la porte principale** fort de ta connaissance\n2. **Explorer l alcove cachee**\n3. **Utiliser du sang sur les runes** pour ouvrir le passage secret",
                    'tokens_used'     => 195,
                    'model_used'      => 'openai/gpt-4o-mini',
                    'created_at'      => now()->subMinutes(17),
                    'updated_at'      => now()->subMinutes(17),
                ],
            ]);
        }

        // ── Conversation 3 : Demo partage (aventurier) ──
        $conv3 = Conversation::where('title', 'Conversation partagee entre deux comptes')
            ->where('user_id', $aventurier->id)
            ->first();

        if (!$conv3) {
            $conv3 = Conversation::create([
                'user_id'      => $aventurier->id,
                'title'        => 'Conversation partagee entre deux comptes',
                'model'        => 'anthropic/claude-3-haiku',
                'total_tokens' => 280,
            ]);

            Message::insert([
                [
                    'conversation_id' => $conv3->id,
                    'role'            => 'user',
                    'content'         => "Cette conversation est partagee avec le compte professeur pour demonstrer la fonctionnalite de partage !",
                    'tokens_used'     => 22,
                    'model_used'      => null,
                    'created_at'      => now()->subMinutes(5),
                    'updated_at'      => now()->subMinutes(5),
                ],
                [
                    'conversation_id' => $conv3->id,
                    'role'            => 'assistant',
                    'content'         => "**Fonctionnalite de partage activee !**\n\nCette conversation illustre le systeme de partage de QuestMaster.\n\nVoici comment ca fonctionne :\n- Le proprietaire invite d autres utilisateurs par email\n- Les membres invites voient la conversation dans leur sidebar sous **Partagees avec moi**\n- Tout le monde peut lire ET repondre\n- Seul le proprietaire peut supprimer la conversation\n\nSi l email invite n est pas encore inscrit, un **email d invitation** est envoye automatiquement !",
                    'tokens_used'     => 145,
                    'model_used'      => 'anthropic/claude-3-haiku',
                    'created_at'      => now()->subMinutes(4),
                    'updated_at'      => now()->subMinutes(4),
                ],
            ]);
        }

        // ── Partager conv3 avec le prof ──
        ConversationMember::firstOrCreate(
            [
                'conversation_id' => $conv3->id,
                'user_id'         => $prof->id,
            ],
            [
                'role'      => 'member',
                'joined_at' => now(),
            ]
        );

        // ── Statistiques utilisation ──
        $this->createUsageStats($prof);
        $this->createUsageStats($aventurier);

        $this->command->info('DemoSeeder execute avec succes !');
        $this->command->info('Prof@ifosup.be / Ifosup');
        $this->command->info('Aventurier@questmaster.be / Quest1234');
        $this->command->info('3 conversations creees avec messages');
        $this->command->info('Conversation partagee entre les deux comptes');
    }



    private function createUsageStats(User $user): void
    {
        $models = [
            'openai/gpt-4o-mini',
            'anthropic/claude-3-haiku',
            'google/gemini-2.0-flash-001',
        ];

        foreach ($models as $model) {
            UserModelUsage::firstOrCreate(
                [
                    'user_id'    => $user->id,
                    'model'      => $model,
                    'usage_date' => now()->toDateString(),
                ],
                [
                    'total_messages' => rand(3, 15),
                    'total_tokens'   => rand(500, 3000),
                ]
            );
        }
    }
}
