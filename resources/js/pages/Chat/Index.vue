<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Conversation {
    id: number
    title: string
    model: string
    updated_at: string
}

interface Model {
    id: string
    name: string
    description: string
    provider: string
}

const props = defineProps<{
    conversations: Conversation[]
    models: Model[]
}>()

const selectedModel = ref(props.models[0]?.id ?? 'openai/gpt-4o-mini')
const isCreating = ref(false)
const showDeleteSuccess = ref(false)

function createConversation() {
    isCreating.value = true
    router.post('/conversations', {
        model: selectedModel.value,
    }, {
        onFinish: () => {
            isCreating.value = false
        },
    })
}

function deleteConversation(id: number, e: Event) {
    e.preventDefault()
    e.stopPropagation()
    if (!confirm('Abandonner cette quête ?')) return

    router.delete(`/conversations/${id}`, {
        onSuccess: () => {
            showDeleteSuccess.value = true
            setTimeout(() => showDeleteSuccess.value = false, 3000)
        },
    })
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
    })
}
</script>

<template>
    <AppLayout title="QuestMaster">
        <div class="flex h-screen bg-gray-50 dark:bg-gray-900 epic:bg-amber-950 overflow-hidden">

            <!-- Sidebar -->
            <aside class="w-72 bg-white dark:bg-gray-800 epic:bg-amber-900/30 border-r border-gray-200 dark:border-gray-700 epic:border-amber-800/50 flex flex-col shrink-0">

                <div class="p-4 border-b border-gray-200 dark:border-gray-700 epic:border-amber-800/50">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white epic:text-amber-300 flex items-center gap-2">
                        🎲 QuestMaster
                    </h1>
                    <p class="text-xs text-gray-400 dark:text-gray-500 epic:text-amber-600 mt-0.5">
                        Ton Maître de Jeu IA
                    </p>
                </div>

                <!-- Sélecteur de modèle -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 epic:border-amber-800/50">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 epic:text-amber-500 mb-2 uppercase tracking-wide">
                        ⚡ Puissance du sort
                    </label>
                    <select
                        v-model="selectedModel"
                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 epic:border-amber-700 bg-white dark:bg-gray-700 epic:bg-amber-900/50 text-gray-900 dark:text-white epic:text-amber-200 px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                    >
                        <option
                            v-for="model in models"
                            :key="model.id"
                            :value="model.id"
                        >
                            {{ model.name }} — {{ model.description }}
                        </option>
                    </select>
                </div>

                <!-- Bouton nouvelle quête -->
                <div class="p-4">
                    <button
                        @click="createConversation"
                        :disabled="isCreating"
                        class="w-full flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 epic:bg-amber-600 epic:hover:bg-amber-700 disabled:opacity-50 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                    >
                        <span v-if="isCreating">Préparation...</span>
                        <span v-else>⚔️ Nouvelle quête</span>
                    </button>
                </div>

                <!-- Liste des quêtes -->
                <nav class="flex-1 overflow-y-auto px-2 pb-4">
                    <p v-if="conversations.length === 0" class="text-center text-sm text-gray-400 dark:text-gray-500 epic:text-amber-700 mt-8">
                        🗺️ Aucune quête en cours
                    </p>

                    <template v-for="conv in conversations" :key="conv.id">
                        <div
                            class="group flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 epic:hover:bg-amber-900/30 transition-colors mb-1 cursor-pointer"
                            @click="router.visit(`/conversations/${conv.id}`)"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white epic:text-amber-200 truncate">
                                    {{ conv.title }}
                                </p>
                                <p class="text-xs text-gray-400 epic:text-amber-700 mt-0.5">
                                    {{ formatDate(conv.updated_at) }}
                                </p>
                            </div>
                            <button
                                @click.stop="deleteConversation(conv.id, $event)"
                                class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition-all ml-2 text-lg leading-none"
                            >
                                &times;
                            </button>
                        </div>
                    </template>
                </nav>
            </aside>

            <!-- Zone principale -->
            <main class="flex-1 flex flex-col items-center justify-center">
                <div class="text-center px-6">
                    <p class="text-7xl mb-6">🎲</p>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white epic:text-amber-300 mb-3">
                        Bienvenue, Aventurier !
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 epic:text-amber-600 mb-2 max-w-md">
                        Choisis ton sort et lance les dés pour commencer une nouvelle aventure épique.
                    </p>
                    <p class="text-gray-400 dark:text-gray-500 epic:text-amber-700 text-sm mb-8 max-w-md">
                        🐉 Des donjons mystérieux t'attendent, des dragons à vaincre et des trésors à découvrir.
                    </p>
                    <button
                        @click="createConversation"
                        :disabled="isCreating"
                        class="bg-purple-600 hover:bg-purple-700 epic:bg-amber-600 epic:hover:bg-amber-700 disabled:opacity-50 text-white rounded-xl px-8 py-4 font-bold text-lg transition-all hover:scale-105"
                    >
                        ⚔️ Lancer les dés
                    </button>
                </div>
            </main>

            <!-- Toast suppression -->
            <div
                v-if="showDeleteSuccess"
                class="fixed bottom-6 right-6 bg-green-700 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 z-50"
            >
                <span>⚔️ Quête abandonnée</span>
                <button
                    @click="showDeleteSuccess = false"
                    class="text-white hover:text-green-200 text-lg leading-none"
                >
                    &times;
                </button>
            </div>

        </div>
    </AppLayout>
</template>
