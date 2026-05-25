<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Instruction {
    persona: string | null
    context: string | null
    response_style: string | null
    language: string | null
    is_active: boolean
}

const props = defineProps<{
    instruction: Instruction | null
}>()

const form = ref({
    persona:        props.instruction?.persona        ?? '',
    context:        props.instruction?.context        ?? '',
    response_style: props.instruction?.response_style ?? '',
    language:       props.instruction?.language       ?? 'fr',
    is_active:      props.instruction?.is_active      ?? true,
})

const saved = ref(false)
const saving = ref(false)

function save() {
    saving.value = true
    router.post('/instructions', form.value, {
        onSuccess: () => {
            saved.value = true
            setTimeout(() => saved.value = false, 3000)
        },
        onFinish: () => {
            saving.value = false
        },
    })
}

const languages = [
    { value: 'fr', label: '🇫🇷 Français' },
    { value: 'en', label: '🇬🇧 English' },
    { value: 'es', label: '🇪🇸 Español' },
    { value: 'de', label: '🇩🇪 Deutsch' },
    { value: 'nl', label: '🇳🇱 Nederlands' },
]
</script>

<template>
    <AppLayout title="Instructions personnalisées">
        <div class="max-w-3xl mx-auto px-6 py-8">

            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Instructions personnalisées
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Configure le comportement de MyBotKnows selon tes préférences.
                    </p>
                </div>
                <button
                    @click="router.visit('/chat')"
                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                >
                    ← Retour au chat
                </button>
            </div>

            <!-- Toggle actif -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6 flex items-center justify-between">
                <div>
                    <h2 class="font-medium text-gray-900 dark:text-white">
                        Activer les instructions
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Les instructions seront appliquées à toutes tes conversations
                    </p>
                </div>
                <button
                    @click="form.is_active = !form.is_active"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                    :class="form.is_active ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'"
                >
                    <span
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                        :class="form.is_active ? 'translate-x-6' : 'translate-x-1'"
                    />
                </button>
            </div>

            <!-- Formulaire -->
            <div class="space-y-6">

                <!-- Persona -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                    <label class="block font-medium text-gray-900 dark:text-white mb-1">
                        Persona du bot
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                        Qui est le bot ? Quel est son rôle ?
                    </p>
                    <textarea
                        v-model="form.persona"
                        rows="3"
                        placeholder="Ex: Tu es un assistant expert en développement web, tu réponds de façon concise et technique."
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
                    />
                </div>

                <!-- Contexte -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                    <label class="block font-medium text-gray-900 dark:text-white mb-1">
                        Ton contexte
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                        Qui es-tu ? Que faut-il savoir sur toi ?
                    </p>
                    <textarea
                        v-model="form.context"
                        rows="3"
                        placeholder="Ex: Je suis étudiant en développement web, je travaille principalement avec Laravel et Vue.js."
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
                    />
                </div>

                <!-- Style de réponse -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                    <label class="block font-medium text-gray-900 dark:text-white mb-1">
                        Style de réponse
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                        Comment le bot doit-il répondre ?
                    </p>
                    <textarea
                        v-model="form.response_style"
                        rows="3"
                        placeholder="Ex: Réponds toujours avec des exemples de code. Utilise des listes pour structurer tes réponses."
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
                    />
                </div>

                <!-- Langue -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                    <label class="block font-medium text-gray-900 dark:text-white mb-1">
                        Langue de réponse
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                        Dans quelle langue le bot doit-il répondre ?
                    </p>
                    <select
                        v-model="form.language"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    >
                        <option
                            v-for="lang in languages"
                            :key="lang.value"
                            :value="lang.value"
                        >
                            {{ lang.label }}
                        </option>
                    </select>
                </div>

            </div>

            <!-- Bouton sauvegarder -->
            <div class="mt-8 flex items-center gap-4">
                <button
                    @click="save"
                    :disabled="saving"
                    class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-xl px-6 py-3 font-medium transition-colors"
                >
                    <span v-if="saving">Sauvegarde...</span>
                    <span v-else>Sauvegarder</span>
                </button>

                <span
                    v-if="saved"
                    class="text-green-600 dark:text-green-400 text-sm font-medium"
                >
                    ✅ Instructions sauvegardées !
                </span>
            </div>

        </div>
    </AppLayout>
</template>
