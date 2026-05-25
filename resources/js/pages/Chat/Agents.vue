<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Agent {
    id: number
    name: string
    emoji: string
    persona: string | null
    context: string | null
    response_style: string | null
    language: string
    model: string
    is_default: boolean
}

interface Model {
    id: string
    name: string
    description: string
    provider: string
}

const props = defineProps<{
    agents: Agent[]
    models: Model[]
}>()

const showForm = ref(false)
const editingAgent = ref<Agent | null>(null)
const saving = ref(false)

const emptyForm = {
    name: '',
    emoji: '🤖',
    persona: '',
    context: '',
    response_style: '',
    language: 'fr',
    model: 'openai/gpt-4o-mini',
    is_default: false,
}

const form = ref({ ...emptyForm })

const languages = [
    { value: 'fr', label: '🇫🇷 Français' },
    { value: 'en', label: '🇬🇧 English' },
    { value: 'es', label: '🇪🇸 Español' },
    { value: 'de', label: '🇩🇪 Deutsch' },
    { value: 'nl', label: '🇳🇱 Nederlands' },
]

function openCreate() {
    editingAgent.value = null
    form.value = { ...emptyForm }
    showForm.value = true
}

function openEdit(agent: Agent) {
    editingAgent.value = agent
    form.value = {
        name:           agent.name,
        emoji:          agent.emoji,
        persona:        agent.persona        ?? '',
        context:        agent.context        ?? '',
        response_style: agent.response_style ?? '',
        language:       agent.language,
        model:          agent.model,
        is_default:     agent.is_default,
    }
    showForm.value = true
}

function closeForm() {
    showForm.value = false
    editingAgent.value = null
    form.value = { ...emptyForm }
}

function save() {
    saving.value = true

    if (editingAgent.value) {
        router.patch(`/agents/${editingAgent.value.id}`, form.value, {
            onSuccess: () => closeForm(),
            onFinish: () => saving.value = false,
        })
    } else {
        router.post('/agents', form.value, {
            onSuccess: () => closeForm(),
            onFinish: () => saving.value = false,
        })
    }
}

function deleteAgent(agent: Agent) {
    if (confirm(`Supprimer l'agent "${agent.name}" ?`)) {
        router.delete(`/agents/${agent.id}`)
    }
}

function startChatWithAgent(agent: Agent) {
    router.post('/conversations', {
        model:    agent.model,
        agent_id: agent.id,
    })
}
</script>

<template>
    <AppLayout title="Mes Agents">
        <div class="max-w-4xl mx-auto px-6 py-8">

            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Mes Agents IA
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Crée des assistants personnalisés avec des comportements spécifiques.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="router.visit('/chat')"
                        class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 font-medium"
                    >
                        ← Retour
                    </button>
                    <button
                        @click="openCreate"
                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-4 py-2 text-sm font-medium transition-colors"
                    >
                        + Nouvel agent
                    </button>
                </div>
            </div>

            <!-- Liste des agents -->
            <div v-if="agents.length === 0" class="text-center py-16">
                <p class="text-5xl mb-4">🤖</p>
                <p class="text-gray-500 dark:text-gray-400 text-lg">
                    Aucun agent créé pour l'instant
                </p>
                <button
                    @click="openCreate"
                    class="mt-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-6 py-3 font-medium transition-colors"
                >
                    Créer mon premier agent
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div
                    v-for="agent in agents"
                    :key="agent.id"
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 flex flex-col gap-4"
                >
                    <!-- En-tête agent -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">{{ agent.emoji }}</span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        {{ agent.name }}
                                    </h3>
                                    <span
                                        v-if="agent.is_default"
                                        class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded-full"
                                    >
                                        Par défaut
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ props.models.find(m => m.id === agent.model)?.name ?? agent.model }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Aperçu persona -->
                    <p
                        v-if="agent.persona"
                        class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2"
                    >
                        {{ agent.persona }}
                    </p>

                    <!-- Actions -->
                    <div class="flex gap-2 mt-auto">
                        <button
                            @click="startChatWithAgent(agent)"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-3 py-2 text-sm font-medium transition-colors text-center"
                        >
                            💬 Démarrer un chat
                        </button>
                        <button
                            @click="openEdit(agent)"
                            class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl px-3 py-2 text-sm transition-colors"
                        >
                            ✏️
                        </button>
                        <button
                            @click="deleteAgent(agent)"
                            class="bg-red-50 dark:bg-red-900/20 hover:bg-red-100 text-red-500 rounded-xl px-3 py-2 text-sm transition-colors"
                        >
                            🗑️
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal formulaire -->
            <div
                v-if="showForm"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
                @click.self="closeForm"
            >
                <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">

                    <!-- Header modal -->
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ editingAgent ? 'Modifier l\'agent' : 'Nouvel agent' }}
                        </h2>
                        <button
                            @click="closeForm"
                            class="text-gray-400 hover:text-gray-600 text-2xl leading-none"
                        >
                            &times;
                        </button>
                    </div>

                    <!-- Corps modal -->
                    <div class="p-6 space-y-5">

                        <!-- Emoji + Nom -->
                        <div class="flex gap-3">
                            <div class="w-24">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Emoji
                                </label>
                                <input
                                    v-model="form.emoji"
                                    type="text"
                                    maxlength="2"
                                    class="w-full text-center text-2xl rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nom de l'agent *
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Ex: Expert Laravel, Assistant Marketing..."
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>
                        </div>

                        <!-- Modèle -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Modèle IA
                            </label>
                            <select
                                v-model="form.model"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
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

                        <!-- Persona -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Persona
                            </label>
                            <textarea
                                v-model="form.persona"
                                rows="3"
                                placeholder="Ex: Tu es un expert Laravel avec 10 ans d'expérience..."
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
                            />
                        </div>

                        <!-- Contexte -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Contexte
                            </label>
                            <textarea
                                v-model="form.context"
                                rows="2"
                                placeholder="Ex: L'utilisateur est un développeur junior..."
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
                            />
                        </div>

                        <!-- Style de réponse -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Style de réponse
                            </label>
                            <textarea
                                v-model="form.response_style"
                                rows="2"
                                placeholder="Ex: Réponds toujours avec des exemples de code..."
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
                            />
                        </div>

                        <!-- Langue -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Langue
                            </label>
                            <select
                                v-model="form.language"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
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

                        <!-- Par défaut -->
                        <div class="flex items-center gap-3">
                            <button
                                @click="form.is_default = !form.is_default"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                                :class="form.is_default ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'"
                            >
                                <span
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                    :class="form.is_default ? 'translate-x-6' : 'translate-x-1'"
                                />
                            </button>
                            <label class="text-sm text-gray-700 dark:text-gray-300">
                                Agent par défaut
                            </label>
                        </div>

                    </div>

                    <!-- Footer modal -->
                    <div class="flex gap-3 p-6 border-t border-gray-200 dark:border-gray-700">
                        <button
                            @click="closeForm"
                            class="flex-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-300 rounded-xl px-4 py-2 text-sm font-medium transition-colors"
                        >
                            Annuler
                        </button>
                        <button
                            @click="save"
                            :disabled="saving || !form.name"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-xl px-4 py-2 text-sm font-medium transition-colors"
                        >
                            {{ saving ? 'Sauvegarde...' : editingAgent ? 'Modifier' : 'Créer' }}
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </AppLayout>
</template>
