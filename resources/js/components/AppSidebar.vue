<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue'
import { Bot, BookOpen, FolderGit2, MessageSquare, Settings, User } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useAppearance } from '@/composables/useAppearance';
import type { NavItem } from '@/types';

const { appearance, updateAppearance } = useAppearance()

const themeIcon = computed(() => {
    if (appearance.value === 'dark') return '🌙'
    if (appearance.value === 'epic') return '⚔️'
    return '☀️'
})

const themeLabel = computed(() => {
    if (appearance.value === 'dark') return 'Donjon (Sombre)'
    if (appearance.value === 'epic') return 'Épique (Or)'
    return 'Lumière (Clair)'
})

function cycleTheme() {
    if (appearance.value === 'light' || appearance.value === 'system') updateAppearance('dark')
    else if (appearance.value === 'dark') updateAppearance('epic')
    else updateAppearance('light')
}

const mainNavItems: NavItem[] = [
    {
        title: 'Quêtes',
        href: '/chat',
        icon: MessageSquare,
    },
    {
        title: 'Mes Agents',
        href: '/agents',
        icon: Bot,
    },
    {
        title: 'Instructions',
        href: '/instructions',
        icon: Settings,
    },
    {
        title: 'Mon Héros',
        href: '/settings/profile',
        icon: User,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'GitHub',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/chat" class="flex items-center gap-2">
                            <span class="text-2xl">🎲</span>
                            <div class="flex flex-col leading-tight">
                                <span class="font-bold text-sm">QuestMaster</span>
                                <span class="text-xs opacity-60">Ton Maître de Jeu IA</span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        @click="cycleTheme"
                        class="cursor-pointer"
                    >
                        <span class="text-base">{{ themeIcon }}</span>
                        <span>{{ themeLabel }}</span>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>

</template>
