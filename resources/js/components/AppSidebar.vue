<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, FolderGit2, MessageSquare, Moon, Settings, Sun, User } from 'lucide-vue-next';
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
import { useAppearance } from '@/composables/useAppearance'
import type { NavItem } from '@/types';

const { appearance, updateAppearance } = useAppearance()

function toggleDark() {
    updateAppearance(appearance.value === 'dark' ? 'light' : 'dark')
}

const mainNavItems: NavItem[] = [
    {
        title: 'MyBotKnows',
        href: '/chat',
        icon: MessageSquare,
    },
    {
        title: 'Instructions',
        href: '/instructions',
        icon: Settings,
    },
    {
        title: 'Mon profil',
        href: '/settings/profile',
        icon: User,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
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
                <!-- Logo -->
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/chat">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <!-- Bouton dark mode -->
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        @click="toggleDark"
                        class="cursor-pointer"
                    >
                        <Moon v-if="appearance !== 'dark'" class="h-4 w-4" />
                        <Sun v-else class="h-4 w-4" />
                        <span>{{ appearance === 'dark' ? 'Mode clair' : 'Mode sombre' }}</span>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>

            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
