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
        description: 'Connecte-toi à MyBotKnows',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Connexion - MyBotKnows" />

    <div class="min-h-screen bg-linear-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center p-4">
        <div class="w-full max-w-md">

            <!-- Logo & Titre -->
            <div class="text-center mb-8">
                <div class="text-6xl mb-4">🤖</div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    MyBotKnows
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2">
                    Connecte-toi pour accéder à ton assistant IA
                </p>
            </div>

            <!-- Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">

                <div
                    v-if="status"
                    class="mb-6 text-center text-sm font-medium text-green-600 bg-green-50 dark:bg-green-900/20 rounded-xl px-4 py-3"
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
                        <Label for="email" class="text-gray-700 dark:text-gray-300 font-medium">
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
                            placeholder="toi@exemple.com"
                            class="rounded-xl border-gray-300 dark:border-gray-600 focus:ring-blue-500"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <!-- Mot de passe -->
                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password" class="text-gray-700 dark:text-gray-300 font-medium">
                                Mot de passe
                            </Label>
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-sm text-blue-600 hover:text-blue-700"
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
                            class="rounded-xl border-gray-300 dark:border-gray-600 focus:ring-blue-500"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <!-- Se souvenir -->
                    <div class="flex items-center gap-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <Label for="remember" class="text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                            Se souvenir de moi
                        </Label>
                    </div>

                    <!-- Bouton connexion -->
                    <Button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-3 font-medium mt-2"
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        {{ processing ? 'Connexion...' : 'Se connecter' }}
                    </Button>

                    <!-- Lien inscription -->
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-100 dark:border-gray-700">
                        Pas encore de compte ?
                        <TextLink :href="register()" :tabindex="5" class="text-blue-600 hover:text-blue-700 font-medium ml-1">
                            Créer un compte
                        </TextLink>
                    </div>
                </Form>
            </div>

            <!-- Footer -->
            <p class="text-center text-xs text-gray-400 mt-6">
                MyBotKnows — Propulsé par OpenRouter 🚀
            </p>
        </div>
    </div>
</template>
