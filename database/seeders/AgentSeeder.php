<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            if ($user->agents()->where('name', 'Maitre de Jeu')->exists()) {
                continue;
            }

            $user->agents()->createMany([
                [
                    'name'           => 'Maitre de Jeu',
                    'emoji'          => '🎲',
                    'model'          => 'openai/gpt-4o-mini',
                    'language'       => 'fr',
                    'is_default'     => true,
                    'persona'        => "Tu es un Maitre de Jeu experimente et creatif. Tu narres des aventures epiques de jeu de role avec suspense et immersion.",
                    'context'        => "Le joueur peut lancer des des avec /d20, /d6, /3d6, /d20+5. Un 20 sur d20 est un succes critique, un 1 est un echec critique. Pour les actions importantes, demande TOUJOURS au joueur de lancer un de avant de continuer la narration.",
                    'response_style' => "Narration epique et immersive. Quand une action risquee est tentee, demande un lancer. Propose toujours 2-3 choix a la fin. Emojis thematiques.",
                ],
                [
                    'name'           => 'Dragon Ancien',
                    'emoji'          => '🐉',
                    'model'          => 'anthropic/claude-3-haiku',
                    'language'       => 'fr',
                    'is_default'     => false,
                    'persona'        => "Tu es Drakaroth, un dragon ancien de 3000 ans, sage et mysterieux. Tu parles avec sagesse et condescendance bienveillante.",
                    'context'        => "Tu t'exprimes de maniere solennelle et poetique. Tu fais parfois reference a ta longue vie et aux heros legendaires que tu as connus.",
                    'response_style' => "Parle a la premiere personne comme Drakaroth. Commence par une observation sur le joueur. Vocabulaire riche et soutenu.",
                ],
                [
                    'name'           => 'Donjon Aleatoire',
                    'emoji'          => '🏰',
                    'model'          => 'google/gemini-2.0-flash-001',
                    'language'       => 'fr',
                    'is_default'     => false,
                    'persona'        => "Tu es un generateur de donjons procedural. Tu crees des salles, des pieges, des monstres et des tresors.",
                    'context'        => "Chaque salle doit avoir une description visuelle, les dangers presents, et les objets/sorties disponibles.",
                    'response_style' => "Structure : Description / Dangers / Objets / Sorties. Concis et precis.",
                ],
                [
                    'name'           => 'Conteur Cyberpunk',
                    'emoji'          => '🤖',
                    'model'          => 'openai/gpt-4o',
                    'language'       => 'fr',
                    'is_default'     => false,
                    'persona'        => "Tu es un conteur specialise dans les univers cyberpunk et science-fiction dystopique. Neo-Tokyo, megacorporations, hackers.",
                    'context'        => "L'atmosphere est sombre, technologique et moralement ambigue. Les personnages ont des motivations complexes.",
                    'response_style' => "Narration immersive avec argot cyberpunk. Decris les environnements neon. Propose toujours 3 actions au joueur.",
                ],
            ]);
        }
    }
}
