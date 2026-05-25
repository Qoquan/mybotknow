<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
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

function createConversation() {
    isCreating.value = true
    router.post('/conversations', {
        model: selectedModel.value,
    }, {
        onSuccess: () => {
            router.visit('/chat')
        },
        onFinish: () => {
            isCreating.value = false
        },
    })
}

function deleteConversation(id: number, e: Event) {
    e.preventDefault()
    e.stopPropagation()
    router.delete(`/conversations/${id}`, {
        preserveScroll: true,
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
    <AppLayout title="MyBotKnows">
        <div class="flex h-screen bg-gray-50 dark:bg-gray-900 overflow-hidden">

            <!-- Sidebar -->
            <aside class="w-72 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col shrink-0">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        🤖 MyBotKnows
                    </h1>
                </div>

                <!-- Sélecteur de modèle -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">
                        Modèle IA
                    </label>
                    <select
                        v-model="selectedModel"
                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
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

                <!-- Bouton nouvelle conversation -->
                <div class="p-4">
                    <button
                        @click="createConversation"
                        :disabled="isCreating"
                        class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                    >
                        <span v-if="isCreating">Création...</span>
                        <span v-else>+ Nouvelle conversation</span>
                    </button>
                </div>

                <!-- Liste des conversations -->
                <nav class="flex-1 overflow-y-auto px-2 pb-4">
                    <p v-if="conversations.length === 0" class="text-center text-sm text-gray-400 mt-8">
                        Aucune conversation
                    </p>

                    <template v-for="conv in conversations" :key="conv.id">
                        <div
                            class="group flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors mb-1 cursor-pointer"
                            @click="router.visit(`/conversations/${conv.id}`)"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ conv.title }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
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
                <div class="text-center">
                    <p class="text-5xl mb-4">🤖</p>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        Bienvenue sur MyBotKnows
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">
                        Sélectionne un modèle et commence une nouvelle conversation
                    </p>
                    <button
                        @click="createConversation"
                        :disabled="isCreating"
                        class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-xl px-6 py-3 font-medium transition-colors"
                    >
                        Nouvelle conversation
                    </button>
                </div>
            </main>

        </div>
    </AppLayout>
</template>
