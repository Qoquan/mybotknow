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
        title: 'Créer un compte',
        description: 'Crée ton compte MyBotKnows',
    },
});
</script>

<template>
    <Head title="Inscription - MyBotKnows" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center p-4">
        <div class="w-full max-w-md">

            <!-- Logo & Titre -->
            <div class="text-center mb-8">
                <div class="text-6xl mb-4">🤖</div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    MyBotKnows
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2">
                    Crée ton compte pour accéder à ton assistant IA
                </p>
            </div>

            <!-- Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-5"
                >
                    <!-- Nom -->
                    <div class="grid gap-2">
                        <Label for="name" class="text-gray-700 dark:text-gray-300 font-medium">
                            Nom complet
                        </Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="name"
                            name="name"
                            placeholder="Jean Dupont"
                            class="rounded-xl border-gray-300 dark:border-gray-600 focus:ring-blue-500"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <!-- Email -->
                    <div class="grid gap-2">
                        <Label for="email" class="text-gray-700 dark:text-gray-300 font-medium">
                            Adresse email
                        </Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            :tabindex="2"
                            autocomplete="email"
                            name="email"
                            placeholder="toi@exemple.com"
                            class="rounded-xl border-gray-300 dark:border-gray-600 focus:ring-blue-500"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <!-- Mot de passe -->
                    <div class="grid gap-2">
                        <Label for="password" class="text-gray-700 dark:text-gray-300 font-medium">
                            Mot de passe
                        </Label>
                        <PasswordInput
                            id="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            name="password"
                            placeholder="••••••••"
                            :passwordrules="passwordRules"
                            class="rounded-xl border-gray-300 dark:border-gray-600 focus:ring-blue-500"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <!-- Confirmation mot de passe -->
                    <div class="grid gap-2">
                        <Label for="password_confirmation" class="text-gray-700 dark:text-gray-300 font-medium">
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
                            class="rounded-xl border-gray-300 dark:border-gray-600 focus:ring-blue-500"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <!-- Bouton inscription -->
                    <Button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-3 font-medium mt-2"
                        tabindex="5"
                        :disabled="processing"
                        data-test="register-user-button"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        {{ processing ? 'Création...' : 'Créer mon compte' }}
                    </Button>

                    <!-- Lien connexion -->
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-100 dark:border-gray-700">
                        Déjà un compte ?
                        <TextLink
                            :href="login()"
                            class="text-blue-600 hover:text-blue-700 font-medium ml-1"
                            :tabindex="6"
                        >
                            Se connecter
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
