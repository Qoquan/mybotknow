<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    auth: {
        user: {
            name: string
        } | null
    }
}>();
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center p-4">
        <div class="text-center">
            <div class="text-8xl mb-6">🤖</div>
            <h1 class="text-5xl font-bold text-gray-900 dark:text-white mb-4">
                MyBotKnows
            </h1>
            <p class="text-xl text-gray-500 dark:text-gray-400 mb-10">
                Ton assistant IA personnel propulsé par les meilleurs modèles du monde
            </p>

            <div class="flex gap-4 justify-center">
                <Link
                    v-if="auth.user"
                    href="/chat"
                    class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-8 py-4 font-medium text-lg transition-colors"
                >
                    Accéder au chat →
                </Link>

                <template v-else>
                    <Link
                        href="/login"
                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl px-8 py-4 font-medium text-lg transition-colors"
                    >
                        Se connecter
                    </Link>
                    <Link
                        href="/register"
                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-8 py-4 font-medium text-lg transition-colors"
                    >
                        Créer un compte
                    </Link>
                </template>
            </div>

            <p class="text-xs text-gray-400 mt-12">
                Propulsé par OpenRouter 🚀
            </p>
        </div>
    </div>
</template>
