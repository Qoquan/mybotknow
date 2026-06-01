<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'Créer un héros',
        description: 'Rejoins QuestMaster',
    },
});
</script>

<template>
    <Head title="Créer un héros - QuestMaster" />

    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-950 to-gray-900 flex items-center justify-center p-4 relative overflow-hidden">

        <!-- Décorations -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-10 right-10 text-5xl opacity-10 animate-pulse">🏰</div>
            <div class="absolute bottom-10 left-10 text-5xl opacity-10 animate-pulse" style="animation-delay:1s">🗡️</div>
            <div class="absolute top-1/2 right-5 text-4xl opacity-10 animate-pulse" style="animation-delay:2s">🔮</div>
            <div class="absolute top-1/3 left-5 text-4xl opacity-10 animate-pulse" style="animation-delay:0.5s">🎲</div>
        </div>

        <div class="w-full max-w-md relative z-10">

            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="text-6xl mb-4">⚔️</div>
                <h1 class="text-4xl font-bold text-white">
                    Quest<span class="text-purple-400">Master</span>
                </h1>
                <p class="text-purple-300 mt-2">
                    🎲 Forge ton destin, crée ton héros
                </p>
            </div>

            <!-- Card -->
            <div class="bg-gray-900/80 backdrop-blur rounded-2xl shadow-2xl border border-purple-900/50 p-8">

                <h2 class="text-lg font-semibold text-white mb-6 text-center">
                    🧙 Création de personnage
                </h2>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-5"
                >
                    <!-- Nom -->
                    <div class="grid gap-2">
                        <Label for="name" class="text-purple-300 font-medium">
                            Nom du héros
                        </Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="name"
                            name="name"
                            placeholder="Aragorn, Hermione..."
                            class="bg-gray-800 border-purple-900 text-white placeholder-gray-600 focus:border-purple-500 rounded-xl"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <!-- Email -->
                    <div class="grid gap-2">
                        <Label for="email" class="text-purple-300 font-medium">
                            Adresse email
                        </Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            :tabindex="2"
                            autocomplete="email"
                            name="email"
                            placeholder="héros@royaume.com"
                            class="bg-gray-800 border-purple-900 text-white placeholder-gray-600 focus:border-purple-500 rounded-xl"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <!-- Mot de passe -->
                    <div class="grid gap-2">
                        <Label for="password" class="text-purple-300 font-medium">
                            Mot de passe secret
                        </Label>
                        <PasswordInput
                            id="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            name="password"
                            placeholder="••••••••"
                            :passwordrules="passwordRules"
                            class="bg-gray-800 border-purple-900 text-white rounded-xl"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <!-- Confirmation -->
                    <div class="grid gap-2">
                        <Label for="password_confirmation" class="text-purple-300 font-medium">
                            Confirmer le mot de passe
                        </Label>
                        <PasswordInput
                            id="password_confirmation"
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="••••••••"
                            :passwordrules="passwordRules"
                            class="bg-gray-800 border-purple-900 text-white rounded-xl"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <!-- Bouton -->
                    <Button
                        type="submit"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white rounded-xl py-3 font-bold mt-2 text-base"
                        tabindex="5"
                        :disabled="processing"
                        data-test="register-user-button"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        {{ processing ? 'Création...' : '🎲 Commencer l\'aventure' }}
                    </Button>

                    <!-- Lien connexion -->
                    <div class="text-center text-sm text-gray-500 pt-2 border-t border-gray-800">
                        Déjà un héros ?
                        <TextLink
                            :href="login()"
                            class="text-purple-400 hover:text-purple-300 font-medium ml-1"
                            :tabindex="6"
                        >
                            Se connecter
                        </TextLink>
                    </div>
                </Form>
            </div>

            <p class="text-center text-xs text-gray-600 mt-6">
                QuestMaster — Propulsé par OpenRouter 🎲
            </p>
        </div>
    </div>
</template>
