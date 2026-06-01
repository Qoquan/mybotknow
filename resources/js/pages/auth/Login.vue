<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Connexion',
        description: 'Connecte-toi à QuestMaster',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Connexion - QuestMaster" />

    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-950 to-gray-900 flex items-center justify-center p-4 relative overflow-hidden">

        <!-- Décorations -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-10 left-10 text-5xl opacity-10 animate-pulse">🐉</div>
            <div class="absolute bottom-10 right-10 text-5xl opacity-10 animate-pulse" style="animation-delay:1s">⚔️</div>
            <div class="absolute top-1/2 left-5 text-4xl opacity-10 animate-pulse" style="animation-delay:2s">🎲</div>
            <div class="absolute top-1/3 right-5 text-4xl opacity-10 animate-pulse" style="animation-delay:0.5s">🔮</div>
        </div>

        <div class="w-full max-w-md relative z-10">

            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="text-6xl mb-4">🎲</div>
                <h1 class="text-4xl font-bold text-white">
                    Quest<span class="text-purple-400">Master</span>
                </h1>
                <p class="text-purple-300 mt-2">
                    ⚔️ Ton Maître de Jeu IA Personnel
                </p>
            </div>

            <!-- Card -->
            <div class="bg-gray-900/80 backdrop-blur rounded-2xl shadow-2xl border border-purple-900/50 p-8">

                <h2 class="text-lg font-semibold text-white mb-6 text-center">
                    🗝️ Accède à ton royaume
                </h2>

                <div
                    v-if="status"
                    class="mb-6 text-center text-sm font-medium text-green-400 bg-green-900/20 rounded-xl px-4 py-3 border border-green-800"
                >
                    {{ status }}
                </div>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-5"
                >
                    <!-- Email -->
                    <div class="grid gap-2">
                        <Label for="email" class="text-purple-300 font-medium">
                            Adresse email
                        </Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            placeholder="héros@royaume.com"
                            class="bg-gray-800 border-purple-900 text-white placeholder-gray-600 focus:border-purple-500 rounded-xl"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <!-- Mot de passe -->
                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password" class="text-purple-300 font-medium">
                                Mot de passe
                            </Label>
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-sm text-purple-400 hover:text-purple-300"
                                :tabindex="5"
                            >
                                Mot de passe oublié ?
                            </TextLink>
                        </div>
                        <PasswordInput
                            id="password"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="bg-gray-800 border-purple-900 text-white rounded-xl"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <!-- Se souvenir -->
                    <div class="flex items-center gap-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <Label for="remember" class="text-sm text-gray-400 cursor-pointer">
                            Se souvenir de moi
                        </Label>
                    </div>

                    <!-- Bouton -->
                    <Button
                        type="submit"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white rounded-xl py-3 font-bold mt-2 text-base"
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        {{ processing ? 'Connexion...' : '⚔️ Entrer dans le royaume' }}
                    </Button>

                    <!-- Lien inscription -->
                    <div class="text-center text-sm text-gray-500 pt-2 border-t border-gray-800">
                        Pas encore de compte ?
                        <TextLink :href="register()" :tabindex="5" class="text-purple-400 hover:text-purple-300 font-medium ml-1">
                            Créer un héros
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
